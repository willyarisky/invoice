<?php

namespace App\Models;

use Throwable;
use Zero\Lib\DB\DBML;
use Zero\Lib\Model;

class Setting extends Model
{
    protected ?string $table = 'settings';

    protected array $fillable = [
        'key',
        'value',
    ];

    protected bool $timestamps = true;

    /** @var array<string, array{label: string, default: string}> */
    private static array $definitions = [
        'business_name' => [
            'label' => 'Company Name',
            'default' => 'OhMyInvoice',
        ],
        'company_logo' => [
            'label' => 'Company Logo',
            'default' => '',
        ],
        'company_address' => [
            'label' => 'Company Address',
            'default' => '',
        ],
        'company_email' => [
            'label' => 'Company Email',
            'default' => '',
        ],
        'company_phone' => [
            'label' => 'Company Phone',
            'default' => '',
        ],
        'default_currency' => [
            'label' => 'Currency',
            'default' => 'USD',
        ],
        'mail_from_address' => [
            'label' => 'From Address',
            'default' => 'hello@example.com',
        ],
        'mail_from_name' => [
            'label' => 'From Name',
            'default' => 'Zero Framework',
        ],
        'mail_mailer' => [
            'label' => 'Mailer',
            'default' => 'smtp',
        ],
        'mail_host' => [
            'label' => 'Mail Host',
            'default' => '127.0.0.1',
        ],
        'mail_port' => [
            'label' => 'Mail Port',
            'default' => '587',
        ],
        'mail_username' => [
            'label' => 'Mail Username',
            'default' => '',
        ],
        'mail_password' => [
            'label' => 'Mail Password',
            'default' => '',
        ],
        'mail_encryption' => [
            'label' => 'Mail Encryption',
            'default' => 'tls',
        ],
        'invoice_email_message' => [
            'label' => 'Invoice Email Message',
            'default' => "Hi {client_name},\n\nPlease find {invoice_no} attached. The total due is {total}.\n\nThanks,\n{company_name}",
        ],
        'primary_accent' => [
            'label' => 'Primary Accent',
            'default' => 'Stone',
        ],
        'notification_emails' => [
            'label' => 'Notification Emails',
            'default' => 'enabled',
        ],
    ];

    /** @var array<string, string>|null */
    private static ?array $resolvedCache = null;

    /** @var array<string, string> */
    private static array $mailEnvMap = [
        'mail_from_address' => 'MAIL_FROM_ADDRESS',
        'mail_from_name' => 'MAIL_FROM_NAME',
        'mail_mailer' => 'MAIL_MAILER',
        'mail_host' => 'MAIL_HOST',
        'mail_port' => 'MAIL_PORT',
        'mail_username' => 'MAIL_USERNAME',
        'mail_password' => 'MAIL_PASSWORD',
        'mail_encryption' => 'MAIL_ENCRYPTION',
    ];

    /** @var array<string, array{name: string, symbol: string, is_default: bool}>|null */
    private static ?array $currencyCache = null;

    /**
     * Return the canonical settings definitions.
     *
     * @return array<string, array{label: string, default: string}>
     */
    public static function definitions(): array
    {
        $definitions = self::$definitions;

        $definitions['mail_from_address']['default'] = (string) env('MAIL_FROM_ADDRESS', $definitions['mail_from_address']['default']);
        $definitions['mail_from_name']['default'] = (string) env('MAIL_FROM_NAME', $definitions['mail_from_name']['default']);
        $definitions['mail_mailer']['default'] = (string) env('MAIL_MAILER', $definitions['mail_mailer']['default']);
        $definitions['mail_host']['default'] = (string) env('MAIL_HOST', $definitions['mail_host']['default']);
        $definitions['mail_port']['default'] = (string) env('MAIL_PORT', $definitions['mail_port']['default']);
        $definitions['mail_username']['default'] = (string) env('MAIL_USERNAME', $definitions['mail_username']['default']);
        $definitions['mail_password']['default'] = (string) env('MAIL_PASSWORD', $definitions['mail_password']['default']);
        $definitions['mail_encryption']['default'] = (string) env('MAIL_ENCRYPTION', $definitions['mail_encryption']['default']);
        $definitions['company_email']['default'] = (string) env('MAIL_FROM_ADDRESS', $definitions['company_email']['default']);

        return $definitions;
    }

    /**
     * Resolve stored settings merged with defaults.
     *
     * @return array<string, string>
     */
    public static function resolved(): array
    {
        if (self::$resolvedCache !== null) {
            return self::$resolvedCache;
        }

        $stored = [];
        try {
            foreach (DBML::table('settings')->select('key', 'value')->get() as $row) {
                $key = (string) ($row['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $stored[$key] = (string) ($row['value'] ?? '');
            }
        } catch (Throwable) {
            $stored = [];
        }

        $resolved = [];

        foreach (self::definitions() as $key => $definition) {
            if (self::isMailKey($key)) {
                $resolved[$key] = self::mailValue($key, $definition['default']);
                continue;
            }
            $resolved[$key] = $stored[$key] ?? $definition['default'];
        }

        self::$resolvedCache = $resolved;

        return $resolved;
    }

    public static function getValue(string $key): string
    {
        $resolved = self::resolved();

        if (array_key_exists($key, $resolved)) {
            return $resolved[$key];
        }

        $definitions = self::definitions();

        return $definitions[$key]['default'] ?? '';
    }

    private static function isMailKey(string $key): bool
    {
        return array_key_exists($key, self::$mailEnvMap);
    }

    private static function mailValue(string $key, string $fallback): string
    {
        $envKey = self::$mailEnvMap[$key] ?? null;
        if ($envKey === null) {
            return $fallback;
        }

        return (string) env($envKey, $fallback);
    }

    public static function currencyPrefix(): string
    {
        return self::currencyPrefixFor(self::getValue('default_currency'));
    }

    public static function currencyPrefixFor(?string $currency): string
    {
        $code = strtoupper(trim((string) $currency));

        if ($code === '') {
            $code = strtoupper(trim((string) self::getValue('default_currency')));
        }

        $currencies = self::currencyData();

        if ($code !== '' && isset($currencies[$code])) {
            $symbol = trim($currencies[$code]['symbol'] ?? '');
            if ($symbol !== '') {
                return $symbol;
            }
        }

        if ($code === '' || $code === 'USD' || $code === '$') {
            return '$';
        }

        return $code . ' ';
    }

    public static function formatMoney(float $amount, ?string $currency = null): string
    {
        $formatted = number_format($amount, 2, '.', ',');

        return self::currencyPrefixFor($currency ?? self::getValue('default_currency')) . ' '. $formatted;
    }

    /**
     * @return array<string, string>
     */
    public static function currencyOptions(): array
    {
        $options = [];

        foreach (self::currencyData() as $code => $data) {
            $options[$code] = $data['name'];
        }

        return $options;
    }

    /**
     * @return array<string, array{name: string, symbol: string, is_default: bool}>
     */
    private static function currencyData(): array
    {
        if (self::$currencyCache !== null) {
            return self::$currencyCache;
        }

        $data = [];

        try {
            $rows = DBML::table('currencies')
                ->select('code', 'name', 'symbol', 'is_default')
                ->orderBy('name')
                ->get();

            foreach ($rows as $row) {
                $code = strtoupper(trim((string) ($row['code'] ?? '')));
                if ($code === '') {
                    continue;
                }
                $data[$code] = [
                    'name' => (string) ($row['name'] ?? $code),
                    'symbol' => (string) ($row['symbol'] ?? ''),
                    'is_default' => (bool) ($row['is_default'] ?? false),
                ];
            }
        } catch (Throwable) {
            $data = [];
        }

        if ($data === []) {
            $data = [
                'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'is_default' => false],
                'EUR' => ['name' => 'Euro', 'symbol' => '', 'is_default' => false],
                'GBP' => ['name' => 'British Pound', 'symbol' => '', 'is_default' => false],
                'PHP' => ['name' => 'Philippine Peso', 'symbol' => '', 'is_default' => false],
                'JPY' => ['name' => 'Japanese Yen', 'symbol' => '', 'is_default' => false],
                'CAD' => ['name' => 'Canadian Dollar', 'symbol' => '', 'is_default' => false],
                'AUD' => ['name' => 'Australian Dollar', 'symbol' => '', 'is_default' => false],
                'SGD' => ['name' => 'Singapore Dollar', 'symbol' => '', 'is_default' => false],
            ];
        }

        self::$currencyCache = $data;

        return $data;
    }
}
