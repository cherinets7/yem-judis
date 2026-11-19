<?php

namespace Database\Seeders;

use App\Enums\CommunityPostType;
use App\Enums\CourtLevel;
use App\Enums\FinalOutcome;
use App\Enums\LawCategory;
use App\Enums\LawJurisdiction;
use App\Enums\OrganizationType;
use App\Enums\PublicCaseType;
use App\Models\CommunityPost;
use App\Models\Law;
use App\Models\Organization;
use App\Models\PublicCaseTracking;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PublicPortalSeeder extends Seeder
{
    public function run(): void
    {
        $zone = Organization::query()->where('type', OrganizationType::Zone)->firstOrFail();
        $saja = Organization::query()->where('type', OrganizationType::City)->firstOrFail();
        $author = User::query()->where('email', 'legal.expert@yemjustice.test')->firstOrFail();

        $this->seedPosts($zone->id, $author->id);
        $this->seedLaws($author->id);
        $this->seedCases($zone->id, $saja->id, $author->id);
    }

    private function seedPosts(int $zoneId, int $authorId): void
    {
        $posts = [
            [CommunityPostType::News, 'የጉዳይ ክትትል በመስመር ላይ ተከፈተ', 'የየም ዞን ፍትህ መምሪያ የታተሙ ወንጀል፣ ፍትሐ ብሔር እና ትግበራ ጉዳዮችን በመዝገብ ቁጥር ወይም በስም መከታተል እንዲቻል የህዝብ ገጽ ከፍቷል።', null],
            [CommunityPostType::News, 'በሳጃ ከተማ የነፃ የህግ ምክር ተጀመረ', 'በሳጃ ከተማ ፍትህ ጽህፈት ቤት ነዋሪዎች በሳምንቱ የመጀመሪያ ሦስት ቀናት ነፃ የህግ ምክር ያገኛሉ። መታወቂያ ይዘው ይምጡ።', null],
            [CommunityPostType::News, 'የ2018 በጀት ዓመት አፈጻጸም ሪፖርት ቀረበ', 'መምሪያው ለዞኑ ምክር ቤት ያቀረበው ሪፖርት የጉዳይ መዝገብ፣ የሰነድ ምዝገባ እና የህግ ትምህርት ስራን ያጠቃልላል።', null],
            [CommunityPostType::Announcement, 'የሰነድ ምዝገባ የአገልግሎት ሰዓት', 'ውክልና፣ ውል እና ማኅበር ምዝገባ ከሰኞ እስከ ፀዐይ ከጠዋቱ ሁለት ሰዓት እስከ ስድስት ሰዓት ድረስ ነው። የምሳ እረፍት ከስድስት እስከ ሰባት ነው።', null],
            [CommunityPostType::Announcement, 'የሲቪክ ማኅበራት እድሳት ጥሪ', 'በዚህ በጀት ዓመት ያልታደሰ ማኅበር የምስክር ወረቀቱን እንዲያድስ ይጠየቃል። የምዝገባ ቁጥርና የአባላት ዝርዝር ይዘው ይቅረቡ።', null],
            [CommunityPostType::Feedback, 'የሰነድ አገልግሎቱ ግልጽ ነው', 'ውክልናዬን በሳጃ ጽህፈት ቤት ሳስመዘግብ የሚያስፈልጉ ወረቀቶች አስቀድመው ተነግረውኛል። አገልግሎቱ ፈጣን ነበር።', 'ሃና ተስፋዬ'],
            [CommunityPostType::LegalJoke, 'ቀጠሮው ከእውነቱ ጋር', "ዳኛው ተከሳሹን ጠየቀ፡ «ለምን ሰዓት አልጠበቅህም?»\nተከሳሹ መለሰ፡ «ቀጠሮዬ ከእውነቱ ጋር አልተስማማም።»", null],
            [CommunityPostType::LegalJoke, 'ከፍርድ ቤት ውጭ', "ጠበቃው ደንበኛውን አረጋገጠ፡ «እውነቱን ትናገራለህ?»\nደንበኛው፡ «አዎ። ከፍርድ ቤት ውጭ ግን።»", null],
        ];

        foreach ($posts as [$type, $title, $content, $guest]) {
            CommunityPost::query()->create([
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'organization_id' => $zoneId,
                'user_id' => $guest === null ? $authorId : null,
                'guest_name' => $guest,
                'is_published' => true,
            ]);
        }

        CommunityPost::query()->create([
            'type' => CommunityPostType::News,
            'title' => 'ያልታተመ ረቂቅ ዜና',
            'content' => 'ይህ ረቂቅ ለህዝብ አይታይም።',
            'organization_id' => $zoneId,
            'user_id' => $authorId,
            'is_published' => false,
        ]);
    }

    private function seedLaws(int $authorId): void
    {
        $laws = [
            [
                'jurisdiction' => LawJurisdiction::Federal,
                'category' => LawCategory::Proclamation,
                'title' => 'የወንጀለኛ መቅጫ ሕግ',
                'law_number' => 'አዋጅ 414/1996',
                'issued_year_ec' => 1996,
                'description' => 'የፌዴራል የወንጀለኛ መቅጫ ሕግ ማጠቃለያ። የወንጀል ክስ፣ ቅጣት እና የሥነ ሥርዓት መርሆዎችን ለዞኑ አቤቱታ ሥራ እንደ ማጣቀሻ ይውላል።',
                'file' => 'laws/proclamation-414-1996.txt',
                'body' => "የወንጀለኛ መቅጫ ሕግ\nአዋጅ ቁጥር 414/1996\n\nይህ ቅጂ የየም ዞን ፍትህ መምሪያ የህዝብ ማጣቀሻ ነው። ሙሉው አዋጅ ከፌዴራል ነጋሪት ጋዜጣ ጋር ይረጋገጣል።\n",
            ],
            [
                'jurisdiction' => LawJurisdiction::Regional,
                'category' => LawCategory::Regulation,
                'title' => 'የክልሉ የሰነድ ምዝገባ ደንብ',
                'law_number' => 'ደንብ 12/2015',
                'issued_year_ec' => 2015,
                'description' => 'ውክልና፣ ውል እና ተዛማጅ ሰነዶች የሚመዘገቡበት የክልል ደንብ። የቴምብር እና የአገልግሎት ክፍያን ያካትታል።',
                'file' => 'laws/regulation-12-2015.txt',
                'body' => "የሰነድ ምዝገባ ደንብ\nቁጥር 12/2015\n\nሰነድ የሚመዘገበው ፊርማ፣ ምስክር እና የተከፈለ ክፍያ ከተረጋገጠ በኋላ ነው።\n",
            ],
            [
                'jurisdiction' => LawJurisdiction::Zonal,
                'category' => LawCategory::Directive,
                'title' => 'የየም ዞን የጉዳይ ሪፖርት መመሪያ',
                'law_number' => 'መመሪያ 3/2018',
                'issued_year_ec' => 2018,
                'description' => 'የወረዳና የከተማ ጽህፈት ቤቶች ሳምንታዊ የወንጀል፣ የፍትሐ ብሔር እና የሰነድ ሪፖርት የሚያቀርቡበት መመሪያ።',
                'file' => 'laws/directive-3-2018.txt',
                'body' => "የጉዳይ ሪፖርት መመሪያ\nቁጥር 3/2018\n\nሪፖርት በየሳምንቱ ከሰኔ 30 ድረስ ባለው የበጀት ዓመት ይቀርባል።\n",
            ],
            [
                'jurisdiction' => LawJurisdiction::Zonal,
                'category' => LawCategory::Manual,
                'title' => 'የህብረተሰብ የህግ ትምህርት ማኑዋል',
                'law_number' => 'ማኑዋል 1/2017',
                'issued_year_ec' => 2017,
                'description' => 'ለቀበሌና ለትምህርት ቤት ግንዛቤ ስልጠና የተዘጋጀ አጭር ማኑዋል። የመብትና የግዴታ መሠረታዊ ነጥቦችን ይዟል።',
                'file' => 'laws/manual-1-2017.txt',
                'body' => "የህግ ትምህርት ማኑዋል\n\nዜጎች ክስ የማቅረብ፣ ምስክር የመሆን እና የህግ ምክር የመጠየቅ መብት አላቸው።\n",
            ],
        ];

        foreach ($laws as $law) {
            Storage::disk('local')->put($law['file'], $law['body']);

            Law::query()->create([
                'jurisdiction' => $law['jurisdiction'],
                'category' => $law['category'],
                'title' => $law['title'],
                'law_number' => $law['law_number'],
                'issued_year_ec' => $law['issued_year_ec'],
                'description' => $law['description'],
                'file_path' => $law['file'],
                'uploaded_by' => $authorId,
            ]);
        }
    }

    private function seedCases(int $zoneId, int $sajaId, int $authorId): void
    {
        $cases = [
            [
                'organization_id' => $sajaId,
                'prosecutor_file_number' => 'YZ/ወ/001/2018',
                'court_file_number' => 'ሳ/ወ/124/2018',
                'plaintiff_name' => 'የየም ዞን ዐቃቤ ህግ',
                'defendant_name' => 'አበበ ከበደ',
                'case_type' => PublicCaseType::CriminalRegular,
                'court_level' => CourtLevel::WoredaCourt,
                'appointment_date_ec' => 'ኅዳር 15 ቀን 2018',
                'appointment_reason' => 'የምስክር ቃል',
                'final_outcome' => FinalOutcome::Pending,
                'is_published' => true,
            ],
            [
                'organization_id' => $sajaId,
                'prosecutor_file_number' => 'YZ/ፍ/014/2018',
                'court_file_number' => 'ሳ/ፍ/088/2018',
                'plaintiff_name' => 'ወ/ሮ አበበች ግርማ',
                'defendant_name' => 'ታደሰ በቀለ',
                'case_type' => PublicCaseType::CivilRegular,
                'court_level' => CourtLevel::WoredaCourt,
                'appointment_date_ec' => 'ታኅሣሥ 4 ቀን 2018',
                'appointment_reason' => 'የድርድር ሙከራ',
                'final_outcome' => FinalOutcome::Pending,
                'is_published' => true,
            ],
            [
                'organization_id' => $zoneId,
                'prosecutor_file_number' => 'YZ/ይ/003/2017',
                'court_file_number' => 'ከ/ይ/019/2017',
                'plaintiff_name' => 'የየም ዞን ዐቃቤ ህግ',
                'defendant_name' => 'መላኩ አለሙ',
                'case_type' => PublicCaseType::CriminalAppeal,
                'court_level' => CourtLevel::HighCourt,
                'appointment_date_ec' => 'ጥር 20 ቀን 2018',
                'appointment_reason' => 'የይግባኝ ክርክር',
                'final_outcome' => FinalOutcome::Decided,
                'is_published' => true,
            ],
            [
                'organization_id' => $zoneId,
                'prosecutor_file_number' => 'YZ/ት/007/2018',
                'court_file_number' => 'ከ/ት/041/2018',
                'plaintiff_name' => 'የሳጃ ከተማ አስተዳደር',
                'defendant_name' => 'ከበደ ወርቁ',
                'case_type' => PublicCaseType::Execution,
                'court_level' => CourtLevel::HighCourt,
                'appointment_date_ec' => 'የካቲት 2 ቀን 2018',
                'appointment_reason' => 'የንብረት ማስፈጸሚያ',
                'final_outcome' => FinalOutcome::Pending,
                'is_published' => true,
            ],
            [
                'organization_id' => $sajaId,
                'prosecutor_file_number' => 'YZ/ሚስጥር/999',
                'court_file_number' => 'ሳ/ሚስጥር/1',
                'plaintiff_name' => 'ያልታተመ ከሳሽ',
                'defendant_name' => 'ያልታተመ ተከሳሽ',
                'case_type' => PublicCaseType::CivilAppeal,
                'court_level' => CourtLevel::WoredaCourt,
                'appointment_date_ec' => null,
                'appointment_reason' => null,
                'final_outcome' => FinalOutcome::ClosedIndefinitely,
                'is_published' => false,
            ],
        ];

        foreach ($cases as $case) {
            PublicCaseTracking::query()->create([
                ...$case,
                'updated_by' => $authorId,
            ]);
        }
    }
}
