<?php

namespace App\Enums;

enum ProsecutorReportType: string
{
    use HasValues;

    case Civil = 'civil';
    case Criminal = 'criminal';
    case DocumentRegistration = 'document_registration';
}
