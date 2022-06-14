<?php

namespace App\Helpers\ExportFile;

use App\Helpers\AppCodes;

/**
 * Class Helper to contain all the logic for the data mapping of each row
 */
class ExportFileRow
{
    public $id;
    public $appCode;
    public $deviceId;
    public $contactable;
    public $subscriptionStatus;
    public $hasDownloadedFreeProductStatus;
    public $hasDownloadedIapProductStatus;

    public function __construct(int $key, array $rowData)
    {
        $appCodes = AppCodes::getInstance();
        $this->id = $key + 1;
        $this->appCode = $appCodes->getCodeByName($rowData['app']);
        $this->deviceId = $rowData['deviceToken'];
        $this->contactable = (!empty($rowData['deviceTokenStatus']) ? $rowData['deviceTokenStatus'] : 0);
        $this->subscriptionStatus = $this->getSubscriptionStatus($rowData['tags']);
        $this->hasDownloadedFreeProductStatus = $this->getHasDownloadedFreeProductStatus($rowData['tags']);
        $this->hasDownloadedIapProductStatus = $this->getHasDownloadedIapProductStatus($rowData['tags']);
    }

    /**
     * Filters string of | separated tags by a filter and returns the first match.
     * If no match return a default
     * @param string $tags
     * @param array $filter
     * @param string $default
     * @return string
     */
    private function filterTags(string $tags, array $filter, string $default)
    {
        $tags = explode("|", $tags);
        $filteredArray = array_filter($tags, function ($tag) use ($filter) {
            return !empty(array_intersect([$tag], $filter));
        });
        if (count($filteredArray) === 0) {
            return $default;
        }
        return reset($filteredArray);
    }

    /**
     * Return subscription status
     * @param string $tags
     * @return string
     * @throws \Exception
     */
    private function getSubscriptionStatus(string $tags)
    {
        try {
            return $this->filterTags($tags, [
                "active_subscriber",
                "expired_subscriber",
                "never_subscribed",
                "subscription_unknown",
            ], "subscription_unknown");
        } catch (\Exception $e) {
            throw new \Exception("Error getting subscription status", 0, $e);
        }
    }

    /**
     * Return the Has Downloaded Free Product Status
     * @param string $tags
     * @return string
     * @throws \Exception
     */
    private function getHasDownloadedFreeProductStatus(string $tags)
    {
        try {
            return $this->filterTags($tags, [
                "has_downloaded_free_product",
                "not_downloaded_free_product",
                "downloaded_free_product_unknown"
            ], "downloaded_free_product_unknown");
        } catch (\Exception $e) {
            throw new \Exception("Error getting Has Downloaded Free Product Status", 0, $e);
        }
    }

    /**
     * Return the Has Downloaded IAP Product Status
     * @param string $tags
     * @return string
     * @throws \Exception
     */
    private function getHasDownloadedIapProductStatus(string $tags)
    {
        try {
            return $this->filterTags($tags, [
                "has_downloaded_iap_product",
                "not_downloaded_free_product", //this was in documentation but thought it may be a typo
                "not_downloaded_iap_product", //included in case above was typo
                "downloaded_iap_product_unknown"
            ], "downloaded_iap_product_unknown");
        } catch (\Exception $e) {
            throw new \Exception("Error getting Has Downloaded IAP Product Status", 0, $e);
        }
    }

    /**
     * Convert the object to an array
     * @return array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "appCode" => $this->appCode,
            "deviceId" => $this->deviceId,
            "contactable" => $this->contactable,
            "subscriptionStatus" => $this->subscriptionStatus,
            "hasDownloadedFreeProductStatus" => $this->hasDownloadedFreeProductStatus,
            "hasDownloadedIapProductStatus" => $this->hasDownloadedIapProductStatus,
        ];
    }
}