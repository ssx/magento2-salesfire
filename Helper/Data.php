<?php
namespace Salesfire\Salesfire\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Salesfire Data Helper
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version.   1.3.0
 */
class Data extends AbstractHelper
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_GENERAL_ENABLED      = 'salesfire/general/is_enabled';
    const XML_PATH_GENERAL_SITE_ID      = 'salesfire/general/site_id';
    const XML_PATH_FEED_ENABLED         = 'salesfire/feed/is_enabled';
    const XML_PATH_FEED_DEFAULT_BRAND   = 'salesfire/feed/default_brand';
    const XML_PATH_FEED_BRAND_CODE      = 'salesfire/feed/brand_code';
    const XML_PATH_FEED_GENDER_CODE     = 'salesfire/feed/gender_code';
    const XML_PATH_FEED_COLOUR_CODE     = 'salesfire/feed/colour_code';
    const XML_PATH_FEED_AGE_GROUP_CODE  = 'salesfire/feed/age_group_code';
    const XML_PATH_FEED_ATTRIBUTE_CODES = 'salesfire/feed/attribute_codes';

    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
    ) {
        $this->storeManager = $storeManager;

        return parent::__construct($context);
    }

    /**
     * What version of salesfire are we using
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.3.0';
    }

    /**
     * Strip the code of anything not normally in an attribute code
     *
     * @param mixed $code
     * @return string
     */
    public function stripCode($code)
    {
        return trim(preg_replace('/[^a-z0-9_]+/', '', strtolower($code ?? "")));
    }

    public function isSingleStoreMode()
    {
        return $this->storeManager->isSingleStoreMode();
    }

    public function getStoreViews()
    {
        if ($this->storeManager->isSingleStoreMode()) {
            $store = new \stdClass;
            $store->id = null;
            $store->site_uuid = $this->getSiteId(null);

            return [$store];
        }

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            $store = new \stdClass;
            $store->id = $storeId;
            $store->site_uuid = $this->getSiteId($storeId);
            $stores[] = $store;
        }

        return $stores;
    }

    /**
     * Whether salesfire is ready to use
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isAvailable($storeId = null)
    {
        $siteId = $this->getSiteId($storeId);
        return ! empty($siteId) && $this->isEnabled($storeId);
    }

    /**
     * Get salesfire enabled flag
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return !! $this->getScopeConfigValue(
            self::XML_PATH_GENERAL_ENABLED,
            $storeId
        );
    }

    /**
     * Get salesfire site id
     *
     * @param string $storeId
     * @return string
     */
    public function getSiteId($storeId = null)
    {
        return $this->getScopeConfigValue(
            self::XML_PATH_GENERAL_SITE_ID,
            $storeId
        );
    }

    /**
     * Get salesfire feed enabled flag
     *
     * @param string $storeId
     * @return string
     */
    public function isFeedEnabled($storeId = null)
    {
        return !! $this->getScopeConfigValue(
            self::XML_PATH_FEED_ENABLED,
            $storeId
        );
    }

    /**
     * Get the default brand
     *
     * @param string $storeId
     * @return string
     */
    public function getDefaultBrand($storeId = null)
    {
        $brand = $this->getScopeConfigValue(
            self::XML_PATH_FEED_DEFAULT_BRAND,
            $storeId
        );

        return trim($brand ?: '');
    }

    /**
     * Get the product brand attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getBrandCode($storeId = null)
    {
        $brand_code = $this->getScopeConfigValue(
            self::XML_PATH_FEED_BRAND_CODE,
            $storeId
        );

        return $this->stripCode($brand_code);
    }

    /**
     * Get the product gender attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getGenderCode($storeId = null)
    {
        $gender_code = $this->getScopeConfigValue(
            self::XML_PATH_FEED_GENDER_CODE,
            $storeId
        );

        return $this->stripCode($gender_code);
    }

    /**
     * Get the product age group attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getAgeGroupCode($storeId = null)
    {
        $age_group_code = $this->getScopeConfigValue(
            self::XML_PATH_FEED_AGE_GROUP_CODE,
            $storeId
        );

        return $this->stripCode($age_group_code);
    }

    /**
     * Get the product colour attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getColourCode($storeId = null)
    {
        $color_code = $this->getScopeConfigValue(
            self::XML_PATH_FEED_COLOUR_CODE,
            $storeId
        );

        return $this->stripCode($color_code);
    }

    /**
     * Get a list of additional codes
     *
     * @param string $storeId
     * @return string
     */
    public function getAttributeCodes($storeId = null)
    {
        $attribute_codes = $this->getScopeConfigValue(
            self::XML_PATH_FEED_COLOUR_CODE,
            $storeId
        );

        return array_map(
            array($this, 'stripCode'),
            explode(',', trim($attribute_codes ?: ''))
        );
    }

    protected function getScopeConfigValue($setting, $storeId)
    {
        if ($storeId) {
            return trim($this->scopeConfig->getValue(
                $setting,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?: '');
        } else {
            return trim($this->scopeConfig->getValue(
                $setting,
            ) ?: '');
        }
    }
}
