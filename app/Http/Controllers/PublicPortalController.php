<?php

namespace App\Http\Controllers;

use App\Enums\CommunityPostType;
use App\Enums\LawCategory;
use App\Enums\LawJurisdiction;
use App\Enums\OrganizationType;
use App\Enums\PublicCaseType;
use App\Models\CommunityPost;
use App\Models\Law;
use App\Models\Organization;
use App\Models\PublicCaseTracking;
use App\Models\Setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicPortalController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'news' => $this->publishedPosts(CommunityPostType::News, 3),
            'announcements' => $this->publishedPosts(CommunityPostType::Announcement, 3),
            'laws' => Law::query()->where('is_published', true)->latest()->take(4)->get(),
        ]);
    }

    public function about(): View
    {
        return view('public.about', [
            'setting' => Setting::current(),
        ]);
    }

    public function services(): View
    {
        return view('public.services', [
            'setting' => Setting::current(),
        ]);
    }

    public function contacts(): View
    {
        return view('public.contacts', [
            'setting' => Setting::current(),
        ]);
    }

    public function cases(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $caseType = $request->string('case_type')->toString();

        $cases = PublicCaseTracking::query()
            ->with('organization')
            ->where('is_published', true)
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$this->escapeLike($search).'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('prosecutor_file_number', 'like', $like)
                        ->orWhere('court_file_number', 'like', $like)
                        ->orWhere('plaintiff_name', 'like', $like)
                        ->orWhere('defendant_name', 'like', $like);
                });
            })
            ->when(
                in_array($caseType, PublicCaseType::values(), true),
                fn ($query) => $query->where('case_type', $caseType),
            )
            ->latest('updated_at')
            ->paginate(8)
            ->withQueryString();

        return view('public.cases', [
            'cases' => $cases,
            'search' => $search,
            'caseType' => $caseType,
            'caseTypes' => PublicCaseType::cases(),
        ]);
    }

    public function laws(Request $request): View
    {
        $jurisdiction = $request->string('jurisdiction')->toString();
        $category = $request->string('category')->toString();

        $laws = Law::query()
            ->where('is_published', true)
            ->when(
                in_array($jurisdiction, LawJurisdiction::values(), true),
                fn ($query) => $query->where('jurisdiction', $jurisdiction),
            )
            ->when(
                in_array($category, LawCategory::values(), true),
                fn ($query) => $query->where('category', $category),
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.laws', [
            'laws' => $laws,
            'jurisdiction' => $jurisdiction,
            'category' => $category,
            'jurisdictions' => LawJurisdiction::cases(),
            'categories' => LawCategory::cases(),
        ]);
    }

    public function downloadLaw(Law $law): StreamedResponse
    {
        abort_unless($law->is_published, 404, 'የህጉ ፋይል አልተገኘም።');

        $path = $law->file_path;

        if (! is_string($path) || $path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(404, 'የህጉ ፋይል አልተገኘም።');
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            abort(404, 'የህጉ ፋይል አልተገኘም።');
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $stem = trim(str_replace(['/', '\\'], '-', (string) ($law->law_number ?: $law->title)));
        $filename = $extension !== '' ? $stem.'.'.$extension : $stem;

        return $disk->download($path, $filename);
    }

    public function community(Request $request): View
    {
        $tab = $this->communityTab($request->string('tab')->toString());

        return view('public.community', [
            'tab' => $tab,
            'posts' => $this->publishedPostsPage($this->tabType($tab)),
        ]);
    }

    public function storeFeedback(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'guest_name.required' => 'ስም ያስፈልጋል።',
            'guest_name.max' => 'ስም በጣም ረጅም ነው።',
            'title.required' => 'ርዕስ ያስፈልጋል።',
            'title.max' => 'ርዕሱ በጣም ረጅም ነው።',
            'content.required' => 'አስተያየት ያስፈልጋል።',
            'content.max' => 'አስተያየቱ በጣም ረጅም ነው።',
        ]);

        $zone = Organization::query()->where('type', OrganizationType::Zone)->first();

        CommunityPost::query()->create([
            'type' => CommunityPostType::Feedback,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'guest_name' => $validated['guest_name'],
            'organization_id' => $zone?->id,
            'user_id' => null,
            'is_published' => false,
        ]);

        return redirect()
            ->route('community.index', ['tab' => 'feedback'])
            ->with('status', 'አስተያየትዎ ተልኳል። ከታተመ በኋላ ይታያል።');
    }

    private function communityTab(string $tab): string
    {
        return in_array($tab, ['news', 'announcements', 'feedback', 'jokes'], true) ? $tab : 'news';
    }

    private function tabType(string $tab): CommunityPostType
    {
        return match ($tab) {
            'announcements' => CommunityPostType::Announcement,
            'feedback' => CommunityPostType::Feedback,
            'jokes' => CommunityPostType::LegalJoke,
            default => CommunityPostType::News,
        };
    }

    /**
     * @return Collection<int, CommunityPost>
     */
    private function publishedPosts(CommunityPostType $type, int $limit)
    {
        return CommunityPost::query()
            ->where('type', $type)
            ->where('is_published', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    private function publishedPostsPage(CommunityPostType $type): LengthAwarePaginator
    {
        return CommunityPost::query()
            ->where('type', $type)
            ->where('is_published', true)
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
