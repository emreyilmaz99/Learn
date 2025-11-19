<?php

namespace App\Services\Interfaces;

use App\Core\Class\ServiceResponse;

interface IMessageSearchService
{
    /**
     * Search messages and return a ServiceResponse.
     *
     * @param string $q
     * @param int $page
     * @param int $perPage
     * @return ServiceResponse
     */
    public function search(string $q, int $page = 1, int $perPage = 20): ServiceResponse;
    
    /**
     * Suggest users by partial name.
     *
     * @param string $partialName
     * @return ServiceResponse
     */
    public function suggestUsers(string $partialName): ServiceResponse;
}
