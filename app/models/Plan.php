<?php
// FILE: /app/models/Plan.php

class Plan extends Model {
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $tenantColumn = null;

    public function getActivePlans() {
        return $this->where(['is_active' => 1], 'price ASC');
    }

    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }

    public function getFeatures($planId) {
        $plan = $this->find($planId);

        if (!$plan || !$plan['features']) {
            return [];
        }

        return json_decode($plan['features'], true);
    }

    public function hasFeature($planId, $feature) {
        $features = $this->getFeatures($planId);
        return in_array($feature, $features);
    }
}
