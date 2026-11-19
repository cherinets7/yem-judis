<?php

namespace App\Enums;

enum PlanDocumentType: string
{
    use HasValues;

    case AnnualPlan = 'annual_plan';
    case AnnualReport = 'annual_report';
}
