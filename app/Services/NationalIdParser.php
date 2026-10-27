<?php

namespace App\Services;

use App\Models\Governorate;
use Carbon\Carbon;

class NationalIdParser
{
    /**
     * Parse Egyptian National ID and extract information
     * 
     * @param string $nationalId
     * @return array|null
     */
    public static function parse(string $nationalId): ?array
    {
        // Remove any spaces or dashes
        $nationalId = preg_replace('/[^0-9]/', '', $nationalId);
        
        // Validate length
        if (strlen($nationalId) !== 14) {
            return null;
        }

        // Validate it's all digits
        if (!ctype_digit($nationalId)) {
            return null;
        }

        try {
            // Extract century digit (1 = 1800s, 2 = 1900s, 3 = 2000s)
            $centuryDigit = (int) substr($nationalId, 0, 1);
            
            // Extract birth date (positions 1-6: YYMMDD)
            $year = (int) substr($nationalId, 1, 2);
            $month = (int) substr($nationalId, 3, 2);
            $day = (int) substr($nationalId, 5, 2);

            // Determine full year based on century digit
            if ($centuryDigit == 2) {
                $fullYear = 1900 + $year;
            } elseif ($centuryDigit == 3) {
                $fullYear = 2000 + $year;
            } else {
                return null; // Invalid century digit
            }

            // Validate date
            if (!checkdate($month, $day, $fullYear)) {
                return null;
            }

            $birthDate = Carbon::createFromDate($fullYear, $month, $day);

            // Extract governorate code (positions 7-8)
            $governorateCode = substr($nationalId, 7, 2);
            $governorate = Governorate::getByCode($governorateCode);

            // Extract gender (position 13 - second digit from right)
            $genderDigit = (int) substr($nationalId, 12, 1);
            $gender = ($genderDigit % 2 === 0) ? 'female' : 'male';

            return [
                'birth_date' => $birthDate->format('Y-m-d'),
                'gender' => $gender,
                'governorate_code' => $governorateCode,
                'governorate_name' => $governorate ? $governorate->getName() : null,
                'governorate_id' => $governorate?->id,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
