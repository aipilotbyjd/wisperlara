<?php

namespace Database\Seeders;

use App\Models\SupportedLanguage;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español'],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français'],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch'],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano'],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português'],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands'],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski'],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский'],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語'],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어'],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文'],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية'],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी'],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe'],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt'],
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย'],
            ['code' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska'],
            ['code' => 'da', 'name' => 'Danish', 'native_name' => 'Dansk'],
            ['code' => 'fi', 'name' => 'Finnish', 'native_name' => 'Suomi'],
            ['code' => 'no', 'name' => 'Norwegian', 'native_name' => 'Norsk'],
            ['code' => 'cs', 'name' => 'Czech', 'native_name' => 'Čeština'],
            ['code' => 'sk', 'name' => 'Slovak', 'native_name' => 'Slovenčina'],
            ['code' => 'hu', 'name' => 'Hungarian', 'native_name' => 'Magyar'],
            ['code' => 'ro', 'name' => 'Romanian', 'native_name' => 'Română'],
            ['code' => 'bg', 'name' => 'Bulgarian', 'native_name' => 'Български'],
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська'],
            ['code' => 'el', 'name' => 'Greek', 'native_name' => 'Ελληνικά'],
            ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית'],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia'],
            ['code' => 'ms', 'name' => 'Malay', 'native_name' => 'Bahasa Melayu'],
            ['code' => 'fil', 'name' => 'Filipino', 'native_name' => 'Filipino'],
        ];

        foreach ($languages as $language) {
            SupportedLanguage::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
