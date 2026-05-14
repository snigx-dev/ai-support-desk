<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum TicketCategory: string
{
    case Access = 'access';
    case Account = 'account';
    case Billing = 'billing';
    case Bug = 'bug';
    case FeatureRequest = 'feature_request';
    case Performance = 'performance';
    case General = 'general';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Access => 'access',
            self::Account => 'account',
            self::Billing => 'billing',
            self::Bug => 'bug',
            self::FeatureRequest => 'feature request',
            self::Performance => 'performance',
            self::General => 'general',
            self::Other => 'other',
        };
    }

    public static function fromLabel(string $label): self
    {
        $normalizedLabel = Str::of($label)
            ->trim()
            ->lower()
            ->replace(['_', '-'], ' ')
            ->value();

        return match ($normalizedLabel) {
            'access', 'login', 'signin', 'sign in', 'authentication' => self::Access,
            'account', 'profile', 'user account' => self::Account,
            'billing', 'invoice', 'payment', 'subscription' => self::Billing,
            'bug', 'error', 'crash', 'defect' => self::Bug,
            'feature request', 'feature', 'enhancement', 'request' => self::FeatureRequest,
            'performance', 'slow', 'latency', 'speed' => self::Performance,
            'general' => self::General,
            default => self::Other,
        };
    }

    public static function fromTicketText(string $text): self
    {
        $normalizedText = Str::of($text)->lower()->value();

        if (str_contains($normalizedText, 'error') || str_contains($normalizedText, 'bug') || str_contains($normalizedText, 'crash') || str_contains($normalizedText, 'outage')) {
            return self::Bug;
        }

        if (str_contains($normalizedText, 'login') || str_contains($normalizedText, 'sign in') || str_contains($normalizedText, 'password')) {
            return self::Access;
        }

        if (str_contains($normalizedText, 'invoice') || str_contains($normalizedText, 'billing') || str_contains($normalizedText, 'payment')) {
            return self::Billing;
        }

        if (str_contains($normalizedText, 'feature') || str_contains($normalizedText, 'request')) {
            return self::FeatureRequest;
        }

        if (str_contains($normalizedText, 'slow') || str_contains($normalizedText, 'performance')) {
            return self::Performance;
        }

        if (str_contains($normalizedText, 'account') || str_contains($normalizedText, 'profile')) {
            return self::Account;
        }

        return self::General;
    }
}
