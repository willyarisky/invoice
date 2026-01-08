<?php

namespace App\Controllers;

use App\Models\Setting;
use Throwable;
use Zero\Lib\DB\DBML;
use Zero\Lib\Http\Request;
use Zero\Lib\Http\Response;
use Zero\Lib\Session;
use Zero\Lib\Validation\ValidationException;

class SettingsController
{
    public function index(): Response
    {
        return $this->company();
    }

    public function company(): Response
    {
        [$status, $errors, $old] = $this->consumeFlash(
            'settings_company_status',
            'settings_company_errors',
            'settings_company_old'
        );

        return view('settings/index', [
            'values' => $this->mergeValues($old),
            'status' => $status,
            'errors' => $errors,
        ]);
    }

    public function currency(): Response
    {
        [$status, $errors, $old] = $this->consumeFlash(
            'settings_currency_status',
            'settings_currency_errors',
            'settings_currency_old'
        );
        $editId = Session::get('currency_edit_id');
        Session::remove('currency_edit_id');

        $currencies = [];
        $defaultCurrency = Setting::getValue('default_currency');

        try {
            $currencies = DBML::table('currencies')
                ->select('id', 'code', 'name', 'symbol', 'is_default', 'created_at')
                ->orderBy('name')
                ->get();

            foreach ($currencies as $row) {
                if (!empty($row['is_default'])) {
                    $defaultCurrency = strtoupper((string) ($row['code'] ?? $defaultCurrency));
                    break;
                }
            }
        } catch (Throwable) {
            $currencies = [];
        }

        return view('settings/currency', [
            'values' => $this->mergeValues($old),
            'status' => $status,
            'errors' => $errors,
            'old' => $old,
            'editId' => $editId,
            'currencies' => $currencies,
            'defaultCurrency' => $defaultCurrency,
        ]);
    }

    public function email(): Response
    {
        [$status, $errors, $old] = $this->consumeFlash(
            'settings_email_status',
            'settings_email_errors',
            'settings_email_old'
        );

        return view('settings/email', [
            'values' => $this->mergeValues($old),
            'status' => $status,
            'errors' => $errors,
        ]);
    }

    public function categories(): Response
    {
        [$status, $errors, $old] = $this->consumeFlash(
            'category_status',
            'category_errors',
            'category_old'
        );
        $editId = Session::get('category_edit_id');
        Session::remove('category_edit_id');

        $categories = [];

        try {
            $categories = DBML::table('categories')
                ->select('id', 'name', 'created_at')
                ->orderBy('name')
                ->get();
        } catch (Throwable) {
            $categories = [];
        }

        return view('settings/categories', [
            'values' => $this->mergeValues([]),
            'status' => $status,
            'errors' => $errors,
            'old' => $old,
            'editId' => $editId,
            'categories' => $categories,
        ]);
    }

    public function taxes(): Response
    {
        [$status, $errors, $old] = $this->consumeFlash(
            'tax_status',
            'tax_errors',
            'tax_old'
        );
        $editId = Session::get('tax_edit_id');
        Session::remove('tax_edit_id');

        $taxes = [];

        try {
            $taxes = DBML::table('taxes')
                ->select('id', 'name', 'rate', 'created_at')
                ->orderBy('name')
                ->get();
        } catch (Throwable) {
            $taxes = [];
        }

        return view('settings/taxes', [
            'values' => $this->mergeValues([]),
            'status' => $status,
            'errors' => $errors,
            'old' => $old,
            'editId' => $editId,
            'taxes' => $taxes,
        ]);
    }

    public function updateCompany(Request $request): Response
    {
        Session::remove('settings_company_status');
        Session::remove('settings_company_errors');
        Session::remove('settings_company_old');

        try {
            $data = $request->validate([
                'business_name' => ['required', 'string', 'min:2'],
                'company_logo' => ['string', 'max:2048'],
                'company_address' => ['string', 'max:500'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('settings_company_errors', $messages);
            Session::set('settings_company_old', $request->all());

            return Response::redirect('/settings');
        }

        $payload = [
            'business_name' => trim((string) $data['business_name']),
            'company_logo' => trim((string) ($data['company_logo'] ?? '')),
            'company_address' => trim((string) ($data['company_address'] ?? '')),
        ];

        $this->saveSettings($payload);

        Session::set('settings_company_status', 'Company settings updated.');

        return Response::redirect('/settings');
    }

    public function updateCurrency(Request $request): Response
    {
        Session::remove('settings_currency_status');
        Session::remove('settings_currency_errors');
        Session::remove('settings_currency_old');

        try {
            $data = $request->validate([
                'default_currency' => ['required', 'string', 'min:3', 'max:4'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('settings_currency_errors', $messages);
            Session::set('settings_currency_old', $request->all());

            return Response::redirect('/settings/currency');
        }

        $currency = strtoupper(trim((string) $data['default_currency']));
        $allowed = array_keys(Setting::currencyOptions());

        if ($allowed === []) {
            $allowed = ['USD'];
        }

        if (!in_array($currency, $allowed, true)) {
            $currency = $allowed[0];
        }

        try {
            $currencyRow = DBML::table('currencies')->where('code', $currency)->first();

            if ($currencyRow !== null) {
                DBML::table('currencies')->update(['is_default' => 0]);
                DBML::table('currencies')->where('code', $currency)->update(['is_default' => 1]);
            }
        } catch (Throwable) {
            // Ignore currency table updates if unavailable.
        }

        $this->saveSettings([
            'default_currency' => $currency,
        ]);

        Session::set('settings_currency_status', 'Currency settings updated.');

        return Response::redirect('/settings/currency');
    }

    public function storeCurrency(Request $request): Response
    {
        Session::remove('settings_currency_status');
        Session::remove('settings_currency_errors');
        Session::remove('settings_currency_old');

        try {
            $data = $request->validate([
                'code' => ['required', 'string', 'min:3', 'max:4', 'unique:currencies,code'],
                'name' => ['required', 'string', 'min:2', 'max:100'],
                'symbol' => ['string', 'max:8'],
                'set_default' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('settings_currency_errors', $messages);
            Session::set('settings_currency_old', $request->all());

            return Response::redirect('/settings/currency');
        } catch (Throwable) {
            Session::set('settings_currency_errors', ['code' => 'Currencies table is not available. Run migrations and try again.']);
            Session::set('settings_currency_old', $request->all());

            return Response::redirect('/settings/currency');
        }

        $code = strtoupper(trim((string) $data['code']));
        $name = trim((string) $data['name']);
        $symbol = trim((string) ($data['symbol'] ?? ''));
        $setDefault = (bool) ($data['set_default'] ?? false);
        $timestamp = date('Y-m-d H:i:s');

        try {
            DBML::table('currencies')->insert([
                'code' => $code,
                'name' => $name,
                'symbol' => $symbol === '' ? null : $symbol,
                'is_default' => $setDefault ? 1 : 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if ($setDefault) {
                DBML::table('currencies')->where('code', '!=', $code)->update(['is_default' => 0]);
                $this->saveSettings(['default_currency' => $code]);
            }
        } catch (Throwable) {
            Session::set('settings_currency_errors', ['code' => 'Currencies table is not available. Run migrations and try again.']);
            Session::set('settings_currency_old', $request->all());

            return Response::redirect('/settings/currency');
        }

        Session::set('settings_currency_status', 'Currency added successfully.');

        return Response::redirect('/settings/currency');
    }

    public function updateCurrencyEntry(int $currency, Request $request): Response
    {
        Session::remove('settings_currency_status');
        Session::remove('settings_currency_errors');
        Session::remove('settings_currency_old');

        try {
            $data = $request->validate([
                'code' => ['required', 'string', 'min:3', 'max:4', 'unique:currencies,code,' . $currency . ',id'],
                'name' => ['required', 'string', 'min:2', 'max:100'],
                'symbol' => ['string', 'max:8'],
                'set_default' => ['boolean'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('settings_currency_errors', $messages);
            Session::set('settings_currency_old', $request->all());
            Session::set('currency_edit_id', $currency);

            return Response::redirect('/settings/currency');
        } catch (Throwable) {
            Session::set('settings_currency_errors', ['code' => 'Currencies table is not available. Run migrations and try again.']);
            Session::set('settings_currency_old', $request->all());
            Session::set('currency_edit_id', $currency);

            return Response::redirect('/settings/currency');
        }

        try {
            $row = DBML::table('currencies')->where('id', $currency)->first();

            if ($row === null) {
                Session::set('settings_currency_errors', ['code' => 'Currency not found.']);
                Session::set('currency_edit_id', $currency);

                return Response::redirect('/settings/currency');
            }

            $code = strtoupper(trim((string) $data['code']));
            $name = trim((string) $data['name']);
            $symbol = trim((string) ($data['symbol'] ?? ''));
            $setDefault = (bool) ($data['set_default'] ?? false);
            $wasDefault = !empty($row['is_default']);
            $timestamp = date('Y-m-d H:i:s');

            if ($setDefault) {
                DBML::table('currencies')->update(['is_default' => 0]);
            }

            DBML::table('currencies')->where('id', $currency)->update([
                'code' => $code,
                'name' => $name,
                'symbol' => $symbol === '' ? null : $symbol,
                'is_default' => $setDefault ? 1 : ($wasDefault ? 1 : 0),
                'updated_at' => $timestamp,
            ]);

            if ($setDefault || $wasDefault) {
                $this->saveSettings(['default_currency' => $code]);
            }
        } catch (Throwable) {
            Session::set('settings_currency_errors', ['code' => 'Currencies table is not available. Run migrations and try again.']);
            Session::set('settings_currency_old', $request->all());
            Session::set('currency_edit_id', $currency);

            return Response::redirect('/settings/currency');
        }

        Session::set('settings_currency_status', 'Currency updated successfully.');

        return Response::redirect('/settings/currency');
    }

    public function deleteCurrency(int $currency): Response
    {
        try {
            $row = DBML::table('currencies')->where('id', $currency)->first();
            $wasDefault = !empty($row['is_default']);

            DBML::table('currencies')->where('id', $currency)->delete();

            if ($wasDefault) {
                $next = DBML::table('currencies')->orderBy('name')->first();
                if ($next !== null) {
                    DBML::table('currencies')->update(['is_default' => 0]);
                    DBML::table('currencies')->where('id', (int) $next['id'])->update(['is_default' => 1]);
                    $this->saveSettings(['default_currency' => (string) ($next['code'] ?? 'USD')]);
                }
            }
        } catch (Throwable) {
            Session::set('settings_currency_errors', ['code' => 'Currencies table is not available. Run migrations and try again.']);

            return Response::redirect('/settings/currency');
        }

        Session::set('settings_currency_status', 'Currency removed.');

        return Response::redirect('/settings/currency');
    }

    public function updateEmail(Request $request): Response
    {
        Session::remove('settings_email_status');
        Session::remove('settings_email_errors');
        Session::remove('settings_email_old');

        try {
            $data = $request->validate([
                'mail_from_address' => ['required', 'email'],
                'mail_from_name' => ['required', 'string', 'min:2'],
                'mail_mailer' => ['required', 'string', 'min:3'],
                'mail_host' => ['required', 'string'],
                'mail_port' => ['required', 'number', 'min:1', 'max:65535'],
                'mail_username' => ['string'],
                'mail_password' => ['string'],
                'mail_encryption' => ['string'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('settings_email_errors', $messages);
            Session::set('settings_email_old', $request->all());

            return Response::redirect('/settings/email');
        }

        $payload = [
            'mail_from_address' => strtolower(trim((string) $data['mail_from_address'])),
            'mail_from_name' => trim((string) $data['mail_from_name']),
            'mail_mailer' => strtolower(trim((string) $data['mail_mailer'])),
            'mail_host' => trim((string) $data['mail_host']),
            'mail_port' => (string) (int) $data['mail_port'],
            'mail_username' => trim((string) ($data['mail_username'] ?? '')),
            'mail_password' => trim((string) ($data['mail_password'] ?? '')),
            'mail_encryption' => strtolower(trim((string) ($data['mail_encryption'] ?? ''))),
        ];

        $allowedEncryption = ['tls', 'ssl', 'none', ''];
        if (!in_array($payload['mail_encryption'], $allowedEncryption, true)) {
            $payload['mail_encryption'] = 'tls';
        }

        $this->saveSettings($payload);

        Session::set('settings_email_status', 'Email settings updated.');

        return Response::redirect('/settings/email');
    }

    public function storeCategory(Request $request): Response
    {
        Session::remove('category_status');
        Session::remove('category_errors');
        Session::remove('category_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2', 'unique:categories,name'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('category_errors', $messages);
            Session::set('category_old', $request->all());

            return Response::redirect('/settings/categories');
        }

        $timestamp = date('Y-m-d H:i:s');

        DBML::table('categories')->insert([
            'name' => trim((string) $data['name']),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        Session::set('category_status', 'Category added successfully.');

        return Response::redirect('/settings/categories');
    }

    public function updateCategory(int $category, Request $request): Response
    {
        Session::remove('category_status');
        Session::remove('category_errors');
        Session::remove('category_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2', 'unique:categories,name,' . $category . ',id'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('category_errors', $messages);
            Session::set('category_old', $request->all());
            Session::set('category_edit_id', $category);

            return Response::redirect('/settings/categories');
        }

        $timestamp = date('Y-m-d H:i:s');

        DBML::table('categories')->where('id', $category)->update([
            'name' => trim((string) $data['name']),
            'updated_at' => $timestamp,
        ]);

        Session::set('category_status', 'Category updated successfully.');

        return Response::redirect('/settings/categories');
    }

    public function deleteCategory(int $category): Response
    {
        DBML::table('categories')->where('id', $category)->delete();

        Session::set('category_status', 'Category removed.');

        return Response::redirect('/settings/categories');
    }

    public function storeTax(Request $request): Response
    {
        Session::remove('tax_status');
        Session::remove('tax_errors');
        Session::remove('tax_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2', 'unique:taxes,name'],
                'rate' => ['required', 'number', 'min:0', 'max:100'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('tax_errors', $messages);
            Session::set('tax_old', $request->all());

            return Response::redirect('/settings/taxes');
        }

        $timestamp = date('Y-m-d H:i:s');

        DBML::table('taxes')->insert([
            'name' => trim((string) $data['name']),
            'rate' => number_format((float) $data['rate'], 2, '.', ''),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        Session::set('tax_status', 'Tax added successfully.');

        return Response::redirect('/settings/taxes');
    }

    public function updateTax(int $tax, Request $request): Response
    {
        Session::remove('tax_status');
        Session::remove('tax_errors');
        Session::remove('tax_old');

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'min:2', 'unique:taxes,name,' . $tax . ',id'],
                'rate' => ['required', 'number', 'min:0', 'max:100'],
            ]);
        } catch (ValidationException $exception) {
            $messages = array_map(
                static fn (array $errors): string => (string) ($errors[0] ?? ''),
                $exception->errors()
            );

            Session::set('tax_errors', $messages);
            Session::set('tax_old', $request->all());
            Session::set('tax_edit_id', $tax);

            return Response::redirect('/settings/taxes');
        }

        $timestamp = date('Y-m-d H:i:s');

        DBML::table('taxes')->where('id', $tax)->update([
            'name' => trim((string) $data['name']),
            'rate' => number_format((float) $data['rate'], 2, '.', ''),
            'updated_at' => $timestamp,
        ]);

        Session::set('tax_status', 'Tax updated successfully.');

        return Response::redirect('/settings/taxes');
    }

    public function deleteTax(int $tax): Response
    {
        DBML::table('taxes')->where('id', $tax)->delete();

        Session::set('tax_status', 'Tax removed.');

        return Response::redirect('/settings/taxes');
    }

    /**
     * @return array{0: string|null, 1: array, 2: array}
     */
    private function consumeFlash(string $statusKey, string $errorKey, string $oldKey): array
    {
        $status = Session::get($statusKey);
        $errors = Session::get($errorKey) ?? [];
        $old = Session::get($oldKey) ?? [];

        Session::remove($statusKey);
        Session::remove($errorKey);
        Session::remove($oldKey);

        return [$status, $errors, $old];
    }

    /**
     * @param array<string, mixed> $old
     * @return array<string, string>
     */
    private function mergeValues(array $old): array
    {
        $values = Setting::resolved();

        foreach ($old as $key => $value) {
            if (array_key_exists($key, $values)) {
                $values[$key] = (string) $value;
            }
        }

        return $values;
    }

    /**
     * @param array<string, string> $payload
     */
    private function saveSettings(array $payload): void
    {
        foreach ($payload as $key => $value) {
            $setting = Setting::query()->where('key', $key)->first();

            if ($setting instanceof Setting) {
                $setting->update(['value' => $value]);
                continue;
            }

            Setting::create([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }
}
