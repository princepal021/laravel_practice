<?php

namespace App\Services;

use App\Models\{
    Countries,
    States,
    Cities
};

class CommonService
{
    protected $countries;
    protected $states;
    protected $cities;
    public function __construct(Countries $countries, States $states, Cities $cities) {
        $this->countries = $countries;
        $this->states = $states;
        $this->cities = $cities;
    }
    public function getCountry() {
        return $this->countries->all();
    }
    public function getAllStates() {
        return $this->states->all();
    }
    public function getStates($countryId) {
        return $this->states->where('country_id', $countryId)->get();
    }
    public function getAllCities() {
        return $this->cities->all();
    }
    public function getCities($stateId) {
        return $this->cities->where('state_id', $stateId)->get();
    }
}