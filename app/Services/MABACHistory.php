<?php

namespace App\Services;

use App\Models\Preferences;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MABACHistory extends MABAC
{
    /**
     * Calculates MABAC based on a specific historic preference.
     * This method overrides or specializes the calculate method from the parent MABAC class
     * if its logic for fetching preferences or alternatives needs to be different for history.
     * However, since the parent `calculate` method now accepts `preferenceId`,
     * we can directly call the parent's calculate method.
     *
     * @param int $userId
     * @param int $preferenceId
     * @return array Calculation result or empty array on failure.
     * @throws \Exception If preference data is not found by parent::calculate.
     */
    public function calculateHistoricMabac(int $userId, int $preferenceId): array
    {
        $result = parent::calculate($userId, $preferenceId);

        if (isset($result['error'])) {
            return $result;
        }

        return $result;
    }
}
