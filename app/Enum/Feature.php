<?php

namespace App\Enum;

enum Feature: string
{
    case FinancialReport = 'financial_report'; // package
    case ThermalPrinter = 'thermal_printer'; // single
    case AdministrationDocuments = 'administration_documents'; // package
    case MeetingManagement = 'meeting_management'; // package
    case EmailDispatch = 'email_dispatch'; // package

    public function label()
    {
        return match ($this) {
            self::FinancialReport => 'Financial Report',
            self::ThermalPrinter => 'Thermal Printer',
            self::AdministrationDocuments => 'Administration Documents',
            self::MeetingManagement => 'Meeting Management',
            self::EmailDispatch => 'Email Dispatch',
        };
    }
}
