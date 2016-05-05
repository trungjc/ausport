<?php

class Glace_Dailydeal_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * date formats
     */

    const DATE_PART = "yyyy-MM-dd";
    const DATETIME_PART = "yyyy-MM-dd HH:mm:ss zzzz";
    const DATETIME_NO_TZ_PART = "yyyy-MM-dd HH:mm:ss";
    const DATETIME_JS_PART = "MMM dd yyyy HH:mm:ss zzz";

    /*
     * config paths
     */

    // magento wide
    const MODULE_DISABLE_OUTPUT_PATH = 'advanced/modules_disable_output/Zizio_Groupsale';

    // zizio wide
    const REFRESH_ENC_KEY_PATH = 'zizio/refresh_enc_key';
    const ZIZIO_REG_PREFIX = 'zizio/reg/';
    const ACCOUNT_NOTIFIED_PATH = 'zizio/reg/account_notified';
    const DBCHANGES_NOTIFIED_PATH = 'zizio/groupsale/dbchanges_notified';
    const ZIZIO_PUB_ID_PATH = 'zizio/reg/zizio_pub_id';
    const ZIZIO_ENC_KEY_PATH = 'zizio/reg/zizio_enc_key';
    const BASE_URL_PATH = 'zizio/settings/base_url';
    const DEBUG_PATH = 'zizio/settings/debug';
    const PORT_PATH = 'zizio/settings/port';
    const PROTOCOL_PATH = 'zizio/settings/protocol';

    // module related
    const DESIGN_SKIN_PATH = 'zizio/groupsale/settings/design_skin';
    const DEALPAGE_CAPTION_PATH = 'zizio/groupsale/settings/dealpage_caption';
    const DEALPAGE_ENABLED_PATH = 'zizio/groupsale/settings/dealpage_enabled';
    const DEALPAGE_TOPLINK_PATH = 'zizio/groupsale/settings/dealpage_toplink';
    const DEALPAGE_URL_PATH = 'zizio/groupsale/settings/dealpage_url';
    const DEALPAGE_ROTATE_PATH = 'zizio/groupsale/settings/dealpage_rotate';
    const DEALPAGE_ONLY_PATH = 'zizio/groupsale/settings/dealpage_only';
    const PROMO_LIMIT_PATH = 'zizio/groupsale/settings/promo_limit';
    const ZIZIO_ACCOUNT_URL_PATH = 'zizio/groupsale/settings/zizio_account_url';
    const ZIZIO_RSS_URL_PATH = 'zizio/groupsale/settings/zizio_rss_url';
    const GROUPSALE_REGISTERED_PATH = 'zizio/groupsale/registered';
    const CRON_HOURLY_LAST_RUN_PATH = 'zizio/groupsale/cron/hourly_last_run';
    const CRON_DAILY_LAST_RUN_PATH = 'zizio/groupsale/cron/daily_last_run';
    const MESSAGES_LAST_GET_PATH = 'zizio/groupsale/messages_last_get';

    // urls
    const SAVE_DEAL_URL = '/group/mage_admin_savesale';
    const SAVE_DEAL_URL_URL = '/group/mage_admin_savedealurl';
    const REFRESH_ENC_KEY_URL = '/group/mage/admin/admin_refresh_enc_key';

    /*
     * config data
     */

    static $BASE_URL = "";
    static $DEBUG = null;
    static $DEALPAGE_CAPTION = null;
    static $DEALPAGE_ENABLED = null;
    static $DEALPAGE_TOPLINK = null;
    static $DEALPAGE_URL = "";
    static $DEALPAGE_ROTATE = "";
    static $DEALPAGE_ONLY = "";
    static $DESIGN_SKIN = "";
    static $EXT_TYPE = "groupsale";
    static $JS_VER = "1.3";
    static $PORT = "";
    static $PROTOCOL = "";
    static $ZIZIO_ACCOUNT_URL = "";
    static $ZIZIO_RSS_URL = "";

    /*
     * runtime data storage
     */
    static $GroupsaleTreated = false;
    static $PathToHelper = null;
    static $ScriptUrlArgs = array();

    /*
     * Constructors
     */

    public function _construct()
    {
        $this->_moduleName = "Zizio_GroupSale";
        parent::_construct();
    }

    /**
     * "Static constructor". Should be invoked only once - at the bottom of this file.
     */
    public static function StaticConstructor()
    {
        // Fetch parameters to connect to our server from the Magento configuration.
        self::$BASE_URL = Mage::getStoreConfig(self::BASE_URL_PATH);
        self::$DEBUG = Mage::getStoreConfig(self::DEBUG_PATH) ? true : false;
        self::$DEALPAGE_CAPTION = Mage::getStoreConfig(self::DEALPAGE_CAPTION_PATH);
        self::$DEALPAGE_ENABLED = Mage::getStoreConfig(self::DEALPAGE_ENABLED_PATH);
        self::$DEALPAGE_TOPLINK = Mage::getStoreConfig(self::DEALPAGE_TOPLINK_PATH);
        self::$DEALPAGE_URL = Mage::getStoreConfig(self::DEALPAGE_URL_PATH);
        self::$DEALPAGE_ROTATE = Mage::getStoreConfig(self::DEALPAGE_ROTATE_PATH);
        self::$DEALPAGE_ONLY = self::$DEALPAGE_ENABLED ? Mage::getStoreConfig(self::DEALPAGE_ONLY_PATH) : false;
        self::$DESIGN_SKIN = Mage::getStoreConfig(self::DESIGN_SKIN_PATH);
        self::$PORT = Mage::getStoreConfig(self::PORT_PATH);
        self::$PROTOCOL = Mage::getStoreConfig(self::PROTOCOL_PATH);
        self::$ZIZIO_ACCOUNT_URL = Mage::getStoreConfig(self::ZIZIO_ACCOUNT_URL_PATH);
        self::$ZIZIO_RSS_URL = Mage::getStoreConfig(self::ZIZIO_RSS_URL_PATH);

        // Note that if config values are missing, we default to hard-coded values:
        if (self::$BASE_URL === null)
            self::$BASE_URL = "widgets.zizio.com";
        if (!self::$DEALPAGE_CAPTION)
            self::$DEALPAGE_CAPTION = "Daily Deal";
        if (self::$DESIGN_SKIN === null)
            self::$DESIGN_SKIN = "v1.0";
        if (self::$PORT === null)
            self::$PORT = "";
        if (self::$PROTOCOL === null)
            self::$PROTOCOL = "https";
        if (self::$ZIZIO_ACCOUNT_URL === null)
            self::$ZIZIO_ACCOUNT_URL = "www.zizio.com/account";
        if (self::$ZIZIO_RSS_URL === null)
            self::$ZIZIO_RSS_URL = "www.zizio.com/deals/rss";

        // Physical path to the location of our helper files (used for watermark image)
        self::$PathToHelper = Mage::getConfig()->getModuleDir('', 'Zizio_Groupsale') . DS . 'Helper';
    }

    public static function IsZizioGroupsaleTreated()
    {
        return self::$GroupsaleTreated;
    }

    public static function IsZizioGroupsaleRegistered()
    {
        $registered = Mage::getModel('core/config_data')->load(self::GROUPSALE_REGISTERED_PATH, 'path');
        return ($registered && ($registered->getValue() == "1"));
    }

    public static function IsEnterpriseEdition()
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        return isset($modulesArray['Enterprise_Enterprise']);
    }

    public static function IsFromDealPage()
    {
        return Mage::app()->getRequest()->getParam('zizio_from_dealpage') ? true : false;
    }

    public static function IsInDealPage()
    {
        return Mage::app()->getRequest()->getParam('zizio_in_dealpage') ? true : false;
    }

    public static function IsOrderItemFromDealPage($item)
    {
        $original_request = $item->getProductOptionByCode('info_buyRequest');
        return $original_request && isset($original_request['zizio_from_dealpage']);
    }

    public static function CheckEncKey()
    {
        if (self::GetConfigValue(self::REFRESH_ENC_KEY_PATH) == "1")
            return;

        $args = array();
        $response = self::CallUrl(self::REFRESH_ENC_KEY_URL, $args, null, true);
        if ($response && isset($response['result'])) {
            if ($response['result'] == 'OK') {
                if (isset($response['enc_key'])) {
                    self::SaveEncriptionKey($response['enc_key']);
                }

                self::SaveConfigValue(self::REFRESH_ENC_KEY_PATH, "1");
            }
        }
    }

    public static function IsNotifiedAboutZizioAccount()
    {
        $notified = Mage::getModel('core/config_data')->load(self::ACCOUNT_NOTIFIED_PATH, 'path');
        return ($notified && ($notified->getValue() == "1"));
    }

    public static function IsNotifiedAboutDbChanges()
    {
        $notified = Mage::getModel('core/config_data')->load(self::DBCHANGES_NOTIFIED_PATH, 'path');
        return ($notified && ($notified->getValue() == "1"));
    }

    public static function SetZizioGroupsaleTreated($treated = true)
    {
        self::$GroupsaleTreated = $treated;
    }

    public static function IsZizioGroupsaleEnabled()
    {
        $zizio_groupsale_disabled = Mage::getStoreConfig(self::MODULE_DISABLE_OUTPUT_PATH); //var_dump($zizio_groupsale_disabled);die;
        return ($zizio_groupsale_disabled != "1");
    }

    public static function GetScriptBlock($src)
    {
        return sprintf("<script type='text/javascript' src='%s'></script>", $src);
    }

    public static function GetPublisherId()
    {
        $pub_id_entry = Mage::getModel('core/config_data')->load(self::ZIZIO_PUB_ID_PATH, 'path');
        if ($pub_id_entry) {
            $pub_id = $pub_id_entry->getValue();
            return $pub_id;
        } else {
            return null;
        }
    }

    public static function GetEncriptionKey()
    {
        $enc_key_entry = Mage::getModel('core/config_data')->load(self::ZIZIO_ENC_KEY_PATH, 'path');
        if ($enc_key_entry) {
            $enc_key = $enc_key_entry->getValue();
            if ($enc_key)
                return $enc_key;
        }
        return null;
    }

    public static function GetToken($args, $base64 = true)
    {
        $enc_key = Glace_Dailydeal_Helper_Data::GetEncriptionKey();

        ksort($args);
        $_args = array();
        foreach ($args as $k => $v)
            $_args[] = "{$k}={$v}";
        $str = md5(implode('&', $_args) . $enc_key);
        //return self::Encrypt($str, $base64);
        return $str;

        /*
          $token = mcrypt_encrypt(MCRYPT_3DES, $enc_key, $str, MCRYPT_MODE_ECB);

          return $base64 ?
          str_replace(array('+', '/'), array('-', '_'), base64_encode($token)) :
          $token;
         */
    }

    public static function Encrypt($data, $base64 = true)
    {
        $enc_key = Zizio_Groupsale_Helper_Data::GetEncriptionKey();
        $token = mcrypt_encrypt(MCRYPT_3DES, $enc_key, $data, MCRYPT_MODE_ECB, "\0\0\0\0\0\0\0\0");

        return $base64 ?
                str_replace(array('+', '/'), array('-', '_'), base64_encode($token)) :
                $token;
    }

    public static function ToBase64($str)
    {
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($str));
    }

    public static function SavePublisherId($zizio_pub_id)
    {
        $pub_id_entry = Mage::getModel('core/config_data')
                ->load(self::ZIZIO_PUB_ID_PATH, 'path')
                ->setValue($zizio_pub_id)
                ->setPath(self::ZIZIO_PUB_ID_PATH)
                ->save();
    }

    public static function SaveEncriptionKey($zizio_enc_key)
    {
        $enc_key_entry = Mage::getModel('core/config_data')
                ->load(self::ZIZIO_ENC_KEY_PATH, 'path')
                ->setValue($zizio_enc_key)
                ->setPath(self::ZIZIO_ENC_KEY_PATH)
                ->save();
    }

    public static function SaveLastRemoteMessagesDate()
    {
        Zizio_Groupsale_Helper_Data::SaveConfigDate(self::MESSAGES_LAST_GET_PATH);
    }

    public static function GetLastRemoteMessagesDate()
    {
        return Zizio_Groupsale_Helper_Data::GetConfigDate(self::MESSAGES_LAST_GET_PATH);
    }

    public static function SaveConfigDate($path)
    {
        self::SaveConfigValue($path, self::DateTimeToString(self::DateTimeToUTC()));
    }

    public static function SaveConfigValue($path, $value)
    {
        $entry = Mage::getModel('core/config_data')
                ->load($path, 'path')
                ->setValue($value)
                ->setPath($path)
                ->save();
    }

    public static function HasConfigValue($path)
    {
        $entry = Mage::getModel('core/config_data')->load($path, 'path');
        if ($entry && $entry->hasValue())
            return true;
        return false;
    }

    public static function GetConfigValue($path)
    {
        $entry = Mage::getModel('core/config_data')->load($path, 'path');
        if ($entry)
            return $entry->getValue();

        return null;
    }

    public static function GetConfigDate($path)
    {
        $entry = Mage::getModel('core/config_data')->load($path, 'path');
        if ($entry && $entry->hasValue())
            return self::DateTimeToUTC($entry->getValue());
        return null;
    }

    public static function RegisterCallback($event, $method, $arguments)
    {
        $events = (array) Mage::registry("zizio_events");
        if (!isset($events[$event]))
            $events[$event] = array();
        $events[$event][] = array($method, $arguments);
        Mage::register("zizio_events", $events, true);
    }

    public static function CallCallbacks($event, $object)
    {
        $events = (array) Mage::registry("zizio_events");
        if (!isset($events[$event]))
            return;
        foreach ($events[$event] as $callback) {
            try {
                if (is_callable(array($object, $callback[0])))
                    call_user_func_array(array($object, $callback[0]), $callback[1]);
                else
                    call_user_func_array(array(new Zizio_Groupsale_Helper_Data(), $callback[0]), $callback[1]);
            } catch (Exception $ex) {
                self::LogError($ex);
            }
        }
        $events[$event] = array();
        Mage::register("zizio_events", $events, true);
    }

    public static function ClearCallbacks($event, $object)
    {
        $events = (array) Mage::registry("zizio_events");
        if (!isset($events[$event]))
            return;
        $events[$event] = array();
        Mage::register("zizio_events", $events, true);
    }

    public static function RemoveStaticBlock($blockId)
    {
        $block = Mage::getModel('cms/block')
                ->load($blockId)
                ->delete();
    }

    public static function CompareVersions($a, $b)
    {
        $a = explode(".", $a);
        $b = explode(".", $b);
        $max_len = max(count($a), count($b));
        for ($i = 0; $i < $max_len; $i++) {
            $diff = array_shift($a) - array_shift($b);
            if ($diff != 0)
                return $diff;
        }
        return 0;
    }

    public static function Register($data = array())
    {
        // check if publisher is already registered
        if (self::IsZizioGroupsaleRegistered())
            return true;

        $session = Mage::getSingleton('admin/session');

        $reg_fields = array(
            'do' => "register",
            'et' => self::GetExtType(),
            'ev' => self::GetExtVer(),
            'uid' => isset($data['zizio_user_id']) ? $data['zizio_user_id'] : "",
            'un' => $session->getZizioUsername(),
            'pw' => $session->getZizioPassword(),
            'mv' => Mage::getVersion(), // magento version
            'me' => self::IsEnterpriseEdition() ? "ent" : "comm", // magento edition
            'e' => $session->getZizioUsername(),
            'fn' => isset($data['firstname']) ? $data['firstname'] : "",
            'ln' => isset($data['lastname']) ? $data['lastname'] : "",
            'p' => isset($data['phone']) ? $data['phone'] : "",
            'sn' => isset($data['storename']) ? $data['storename'] : ""
        );
        if (self::GetPublisherId())
            $reg_fields['id'] = self::GetPublisherId(); // existing publisher id





            
// ugly json fix to ensure 1st level array is associative and 2nd level is numeric
        $store_ids = new StdClass();
        foreach (Zizio_GroupSale_Helper_Data::GetStoresData(true) as $k => $v)
            $store_ids->{$k} = $v;
        $reg_fields['si'] = json_encode($store_ids);

        $response = self::SendUrl(self::GetRegisterUrl(), $reg_fields);
        if ($response) {
            $response = self::json_decode(trim($response, "()"), true);
        }
        if (!$response || !isset($response['register']) || ($response['register'] != "success")) {
            $session = Mage::getSingleton('adminhtml/session');
            if ($session) {
                $error = "Unknown error, Please contact support@zizio.com";

                if (is_array($response)) {
                    if (isset($response['invalid_email']))
                        $error = "Invalid Zizio Account e-mail address, click 'Switch account' to login again.";
                    elseif (isset($response['login_incorrect']))
                        $error = "Invalid Zizio Account username / password, click 'Switch account' to login again.";
                }

                $session->addError("Zizio registration error - {$error}");
            }
            return false;
        }

        // don't overwrite existing settings
        $data = array();
        if (isset($response['pub_id']) && !self::GetPublisherId()) {
            $data['zizio_pub_id'] = $response['pub_id'];
        }
        if (isset($response['enc_key']) && (!self::GetEncriptionKey() || isset($_data['zizio_pub_id']))) {
            $data['zizio_enc_key'] = $response['enc_key'];
        }

        // save data
        self::_SaveRegData($data);

        // initialize
        self::SaveLastRemoteMessagesDate();
        self::SaveConfigDate(self::CRON_HOURLY_LAST_RUN_PATH);
        self::SaveConfigDate(self::CRON_DAILY_LAST_RUN_PATH);
        self::SendAdminPing(self::GetPublisherId());
        self::InitializeSettings();

        // mark publisher registered
        $registered = Mage::getModel('core/config_data')
                ->setValue("1")
                ->setPath(self::GROUPSALE_REGISTERED_PATH)
                ->save();

        return true;
    }

    public static function _SaveRegData($data)
    {
        foreach ($data as $key => $item) {
            $path = self::ZIZIO_REG_PREFIX . $key;
            $entry = Mage::getModel('core/config_data')
                    ->load($path, 'path')
                    ->setValue($item)
                    ->setPath($path)
                    ->save();
        }
    }

    public static function UpdateGroupsalePrice($updates)
    {
        // send update to zizio
        $url = sprintf("%s://%s%s/group/admin_update_price", self::$PROTOCOL, self::$BASE_URL, self::$PORT);
        foreach ($updates as $id => $update)
            $updates[$id] = self::json_encode($update);
        $updates['v'] = 1;
        self::SendUrl($url, $updates);
    }

    /*
     * Returns the version of the extension that's currently installed as a string. ex: "0.4.4"
     * $default - a default value to be returned in case the version cannot be retrieved.
     */

    public static function GetExtVer($default = null)
    {
        $config = Mage::getConfig();
        if ($config != null) {
            $moduleConfig = $config->getModuleConfig('Zizio_Groupsale');
            if ($moduleConfig != null) {
                return (string) $moduleConfig->version;
            }
        }
        return $default;
    }

    public static function GetExtType()
    {
        return self::$EXT_TYPE;
    }

    public static function SendAdminPing($zizio_pub_id)
    {
        $url = sprintf("%s://%s%s/group/admin_mage_ping", self::$PROTOCOL, self::$BASE_URL, self::$PORT);
        $data = array();
        $data['pub_id'] = $zizio_pub_id;
        $data['ext_type'] = self::GetExtType();
        $data['ext_ver'] = self::GetExtVer("");
        self::SendUrl($url, $data);
    }

    public static function SendAdminUpdateOrder($data)
    {
        return self::CallUrl("/group/admin_update_order", array(), $data, true);
    }

    public static function SendAdminUpdatePubData($data)
    {
        return self::CallUrl("/group/admin_update_pub_data", array(), $data, true);
    }

    public static function SendAdminGetPubData($data)
    {
        return self::CallUrl("/group/admin_get_pub_data", array(), $data, true);
    }

    public static function DeleteGroupSale($groupsale)
    {
        $data = array();
        $data['pub_id'] = self::GetPublisherId();
        $data['sale_id'] = $groupsale->getZizioObjectId();
        self::CallUrl("/group/mage_admin_delsale", null, $data, true);
    }

    /*
     * Returns an array containing details about the latest version of the extension, fetched from
     * the Zizio server.
     */

    public static function GetRemoteMessages()
    {
        $last_call = self::GetLastRemoteMessagesDate();
        if ($last_call == null)
            $last_call = self::DateTimeToUTC(null);

        $url = sprintf("%s://%s%s/group/get_messages?last_get=%s&pub_id=%s&mage_ver=%s&ext_ver=%s", self::$PROTOCOL, self::$BASE_URL, self::$PORT, urlencode(self::DateTimeToString($last_call, false)), urlencode(self::GetPublisherId()), urlencode(Mage::getVersion()), urlencode(self::GetExtVer())
        );
        $response = self::SendUrl($url, null);
        if ($response)
            return json_decode($response, true);
        else
            return null;
    }

    /*
     * Logs an exception by sending it to our remote server.
     */

    public static function LogError($ex)
    {
        try {
            $url = sprintf("%s://%s%s/s/log_remote_error", self::$PROTOCOL, self::$BASE_URL, self::$PORT);

            $data = array();
            $data['pub_id'] = self::GetPublisherId();
            $data['msg'] = $ex->getMessage();
            $data['code'] = $ex->getCode();
            $data['file'] = $ex->getFile();
            $data['line'] = $ex->getLine();
            $data['ext_ver'] = self::GetExtVer("");

            $trace = $ex->getTrace();
            if ($trace == null)
                $trace = debug_backtrace();

            $count = 0;
            $data["stack_trace"] = '';
            foreach ($trace as $i => $step) {
                $row = sprintf("%s: %s at line %s\r\n", isset($step['function']) ? $step['function'] : '', isset($step['file']) ? $step['file'] : '', isset($step['line']) ? $step['line'] : '');
                $data["stack_trace"] .= $row;
                $count++;
                if ($count > 20)
                    break;
            }

            self::SendUrl($url, $data);
        } catch (Exception $e) {
            
        }

        // If we're running inside a unit test, rethrow the exception so the unit test
        // knows about it and fails accordingly.
        if ((isset($GLOBALS["zizio_test_running"])) && ($GLOBALS["zizio_test_running"])) {
            throw $ex;
        }
    }

    public static function CallUrl($relative_url, $args, $post_fields, $safe = true)
    {
        if ($args === null)
            $args = array();
        $url = self::BuildScriptUrl($relative_url, $args, false);
        if ($safe)
            $response = self::SendSafeUrl($url, $post_fields);
        else
            $response = self::SendUrl($url, $post_fields);

        $response = json_decode($response, true);
        return $response;
    }

    public static function SendUrl($url, $post_fields)
    {
        try {
            if (extension_loaded('curl')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, "Zizio Mage Admin");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                if ($post_fields)
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
                $responseBody = curl_exec($ch);

                // check http code
                $info = curl_getinfo($ch);
                $code = isset($info['http_code']) ? $info['http_code'] : 0;
                if ($code != 200) {
                    $session = Mage::getSingleton('adminhtml/session');
                    if ($session)
                        $session->addError(sprintf('Curl returned %s HTTP code for URL "%s". Contact your IT or support@zizio.com', $code, $url));
                    return false;
                }

                curl_close($ch);

                return $responseBody;
            }
            else {
                $session = Mage::getSingleton('adminhtml/session');
                if ($session)
                    $session->addError('No curl extension. Contact your IT or support@zizio.com');
                return false;
            }
        } catch (Exception $e) {
            $session = Mage::getSingleton('adminhtml/session');
            if ($session)
                $session->addError('Error in curl_exec execution. Contact your IT or support@zizio.com');
            return false;
        }
    }

    public static function SendSafeUrl($url, $post_data)
    {
        if ($post_data)
            $data = self::json_encode($post_data);
        else
            $data = '{}';

        $token_args = array(
            'pl' => $data,
            'pub_id' => self::GetPublisherId(),
            'trans_id' => uniqid()
        );
        $token_args['_token'] = self::ToBase64(self::GetToken($token_args, true));
        return self::SendUrl($url, $token_args);
    }

    public static function ParseNodeAttributes($raw_attributes)
    {
        $matched_attributes = array();
        $attributes = array();
        if (preg_match_all('/(\w+)\s*=\s*(["\'])(.*?)\2/', $raw_attributes, $matched_attributes, PREG_SET_ORDER))
            foreach ($matched_attributes as $attribute)
                $attributes[$attribute[1]] = $attribute[3];
        return $attributes;
    }

    public static function BuildNode($node_name, $attributes, $omittag = true)
    {
        $node_name = trim($node_name);
        $html_attributes = '';
        foreach ($attributes as $name => $val)
            $html_attributes .= "{$name}=\"" . htmlentities($val) . "\" ";
        if ($html_attributes != '')
            $html_attributes = " " . substr($html_attributes, 0, -1);
        $omittag = $omittag ? '' : ' /';
        return "<{$node_name}{$html_attributes}{$omittag}>";
    }

    /*
     * $rule_name - the name for the price rule which is also the coupon code.
     * $rule_desc - text description explaining the price rule (will appear in shopping cart)
     */

    public static function CreateDummyPriceRule($rule_name, $rule_desc)
    {
        $cg_ids = Mage::getResourceModel('customer/group_collection')->load()->getAllIds();
        $website_ids = Mage::getModel('core/website')->getCollection()->getAllIds();
        $from_date = self::DateTimeToDateString(self::DateTimeToStoreTZ()->addDay(-1));

        // create new dummy rule
        $rule = Mage::getModel('salesrule/rule')->load($rule_name, "name");
        $rule->setName($rule_name)
                ->setCouponCode($rule_name)
                ->setDescription($rule_desc)
                ->setStoreLabels(array($rule_desc))
                ->setWebsiteIds($website_ids)
                ->setCustomerGroupIds($cg_ids)
                ->setIsActive(1)
                ->setProductIds('')
                ->setDiscountAmount(0)
                ->setDiscountQty(null)
                ->setDiscountStep('0')
                ->setSimpleFreeShipping('0')
                ->setApplyToShipping('0')
                ->setCouponType(2)
                ->setStopRulesProcessing(false)
                ->setFromDate($from_date);
        $rule->save();
    }

    public static function json_decode($data, $as_array = false)
    {
        if (function_exists('json_decode'))
            return json_decode($data, $as_array);

        // TODO: implement json_decode
        return null;
    }

    public static function json_encode($data)
    {
        if (function_exists('json_encode'))
            return json_encode($data);

        // TODO: implement json_encode
        return null;
    }

    public static function GetProductImage($product, $small)
    {
        if ($small)
            return Mage::getBaseUrl('media') . 'catalog/product' . $product->getSmallImage();
        else
            return Mage::getBaseUrl('media') . 'catalog/product' . $product->getImage();

        // TODO: this code is probably better though it produces temporary files:
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        return (string) Mage::helper('catalog/image')->init($product, 'thumbnail', $product->getSmallImage())->resize(50);
    }

    public static function IsA($object, $class_name)
    {
        return ((get_class($object) == $class_name) || is_subclass_of($object, $class_name));
    }

    public static function GetGmtOffset($timezoneString = null)
    {
        if ($timezoneString == null) {
            $timezoneString = Mage::app()->getStore()->getConfig('general/locale/timezone');
        }
        $zone = new DateTimeZone($timezoneString);
        $now = new DateTime("now", $zone);
        return $zone->getOffset($now);
    }

    //TODO Refactor all uses of Mage::getModel('core/date')->gmtDate() and Mage::getModel('core/date')->date()
    //TODO unit tests!
    /**
     * Returns a string representation of a date converted to store timezone and store Locale
     *
     * @param   string|integer|Zend_Date|array|null $dateTime date in UTC
     * @return  string
     */
    public static function DateTimeToString($dateTime, $includeTZ = true)
    {
        if ($includeTZ) {
            return $dateTime->toString(self::DATETIME_PART);
        } else {
            return $dateTime->toString(self::DATETIME_NO_TZ_PART);
        }
    }

    /**
     * Returns a string representation of the date (year-month-day) part of a date
     * converted to store timezone and store Locale
     *
     * @param   string|integer|Zend_Date|array|null $dateTime date in UTC
     * @return  string
     */
    public static function DateTimeToDateString($dateTime)
    {
        return $dateTime->toString(self::DATE_PART);
    }

    /**
     * Returns a string representation of a date converted to js format
     *
     * @param   Zend_Date $dateTime date in UTC
     * @return  string
     */
    public static function DateTimeToJsString($dateTime)
    {
        return $dateTime->toString(self::DATETIME_JS_PART, null, 'en');
    }

    /**
     * Returns a string representation of a date converted to store timezone and store Locale
     *
     * When sending strings to this method they should always be formatted according to self::DATETIME_PART !
     *
     * @param   string|integer|Zend_Date|array|null $dateTime date in UTC
     * @return  string
     */
    public static function DateTimeToStoreTZ($dateTime = null)
    {
        return Mage::app()->getLocale()->date($dateTime, self::DATETIME_PART, null, true);
        // This method (storeDate())does not accept datetime part and assumes something like YYYY-DD-MM hh:mm:ss which
        // is bad for us. date() is better because does the same work and allows us to supply our self::DATETIME_PART.
        //return Mage::app()->getLocale()->storeDate(null, $dateTime, true);
    }

    /**
     * Returns a string representation of a date converted to UTC and store Locale
     *
     * When sending strings to this method they should always be formatted according to self::DATETIME_PART !
     *
     * @param   string|integer|Zend_Date|array|null $dateTime date in store's timezone
     * 			if null, the the current time now in UTC is returned.
     * @return  string
     */
    public static function DateTimeToUTC($dateTime = null)
    {
        $locale = Mage::app()->getLocale();
        $dateObj = $locale->storeDate(null, $dateTime, true);
        if ($dateTime != null)
            $dateObj->set($dateTime, self::DATETIME_PART);
        $dateObj->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
        return $dateObj;
    }

    /**
     * @deprecated
     */
    public static function GetUTCDateTime($date)
    {
        $date = preg_replace(
                array("|Etc/GMT\+(\d+)$|", "|Etc/GMT-(\d+)$|"), array("GMT-$1", "GMT+$1"), $date
        );
        return gmdate("Y-m-d H:i:s", strtotime($date));
    }

    public static function GetCurrencyCodeHtml()
    {
        return '[' . Mage::app()->getStore()->getBaseCurrency()->getCode() . ']';
    }

    public static function GetBaseCurrencySymbol()
    {
        try {
            $base_currency_code = self::GetBaseCurrencyCode();
            $base_currency = Mage::app()->getLocale()->currency($base_currency_code);
            return $base_currency->getSymbol(
                            $base_currency_code, self::GetLocaleCode());
        } catch (Exception $ex) {
            self::LogError($ex);
            return Mage::app()->getStore()->getBaseCurrencyCode();
        }
    }

    public static function GetBaseCurrencyCode()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }

    public static function GetSupportLinkHtml()
    {
        return '<a href="mailto:support@zizio.com" style="color:red;"><strong>Need Help? Contact Zizio!</strong></a>';
    }

    public static function GetWebsitesBaseUrls()
    {
        $websites = array();
        foreach (Mage::app()->getWebsites(true) as $website) {
            $id = $website->getId();
            $store = $website->getDefaultStore();
            $url = $store ? $store->getBaseUrl() : null;
            $websites[$id] = $url;
        }
        return $websites;
    }

    public static function GetStoresBaseUrls($store_id = null)
    {
        $stores = array();
        foreach (Mage::app()->getWebsites(true) as $website) {
            foreach ($website->getStoreCollection() as $store) {
                if (!is_null($store_id) && $store_id == $store->getId())
                    return $store->getBaseUrl();
                $stores[$store->getId()] = $store->getBaseUrl();
            }
        }
        if (!is_null($store_id))
            return null;
        return $stores;
    }

    public static function GetStoresData($ids_only = false)
    {
        $stores = array();
        foreach (Mage::app()->getWebsites(true) as $website) {
            $website_id = (string) $website->getId();
            if ($ids_only)
                $stores[$website_id] = array();
            else
                $stores[$website_id] = array(
                    'name' => $website->getName(),
                    'urls' => array()
                );
            foreach ($website->getStoreCollection() as $store) {
                $id = (string) $store->getId();
                if ($ids_only)
                    $stores[$website_id][] = $id;
                else
                    $stores[$website_id]['urls'][$id] = array(
                        'name' => $store->getName(),
                        'url' => $store->getBaseUrl()
                    );
            }
        }
        return $stores;
    }

    /*
     * Get the baseUrl for a given Website. Note that baseUrl is actually a property
     * of Store so we need to find a Store in the Website to get to it.
     */

    public static function GetBaseUrlForWebsite($websiteId)
    {
        $app = Mage::app();
        if ($app != null) {
            $website = $app->getWebsite($websiteId);
            if ($website != null) {
                // Try to get the default store for this website:
                $store = $website->getDefaultStore();
                // If there's not default store, get the first store:
                if ($store == null) {
                    foreach ($website->getStoreCollection() as $store) {
                        $store = $store->getBaseUrl();
                        break;
                    }
                }
                if ($store != null)
                    return $store->getBaseUrl();
            }
        }
        // If we're unable to determine the store, retun null:
        return null;
    }

    /*
     * Add the Groupsale "watermark" to the product's image and assign the new image to the product.
     */

    public static function AssignGroupSaleWatermarkToProduct($groupsale, $product, $attributeName)
    {
        if (!$product->getData($attributeName))
            return;

        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        // fix bug where the product url part does not begin with a directory separator:
        $productUrlPart = $product->getData($attributeName);
        if ((strlen($productUrlPart) > 0) && ($productUrlPart[0] != '/') && ($productUrlPart[0] != '\\')) {
            $productUrlPart = DS . $productUrlPart;
        }
        // $originalImagePath ex: C:\projects\zizio\server\zizio_magento\media\catalog\product/n/o/nokia-2610-phone-2.jpg
        $originalImagePath = $baseMediaPath . $productUrlPart;
        $pathinfo = pathinfo($originalImagePath);
        if (!isset($pathinfo['extension'])) {
            $pathinfo['extension'] = 'jpg';
        }

        // $newImagePath ex: C:\projects\zizio\server\zizio_magento\media\catalog\product\cache\zizio\groupsale\nokia-2610-phone-2.200145.jpg
        $newImagePath = $baseMediaPath . DS . 'cache' . DS . 'zizio' . DS . 'groupsale' . DS . $pathinfo['filename'] . '.' . $groupsale->GetId() . '.' . $pathinfo['extension'];

        // The path to the new image is also our cache key. If the file exists on disk, no need to create it:
        if (!file_exists($newImagePath)) {
            // Take the original image and put our groupsale watermark on it. Then save it as a new image.
            try {
                $image = new Varien_Image($originalImagePath);
            } catch (Exception $ex) {
                return;
            }

            if ((method_exists($image, 'getOriginalWidth')) && (method_exists($image, 'getOriginalHeight'))) {
                $originalWidth = $image->getOriginalWidth();
                $originalHeight = $image->getOriginalHeight();
            } else {
                // In older versions of Magento (<1.4.0) where the methods to not exist in Varien_Image, we
                // go directly to GD and access the file for the data:
                list($originalWidth, $originalHeight, $originalType, $originalAttr) = getimagesize($originalImagePath);
            }

            if ($originalWidth > $originalHeight) {
                $image->setWatermarkWidth($originalWidth * 0.6);
                $image->setWatermarkHeigth($originalWidth * 0.15);
            } else {
                $image->setWatermarkWidth($originalHeight * 0.6);
                $image->setWatermarkHeigth($originalHeight * 0.15);
            }

            // In newer versions of Magento(>= 1.4.0) we are forced to use this method, otherwise the watermark has
            // opacity 0 regardless of what we pass in the call to ->watermark() below (!)
            if (method_exists($image, 'setWatermarkImageOpacity')) {
                $image->setWatermarkImageOpacity(100);
            }

            if ($groupsale->getDealType() == 1) {
                $image->watermark(self::$PathToHelper . DS . 'mark_groupsale.gif', 0, 0, 100, false);
            } else if ($groupsale->getDealType() == 2) {
                $image->watermark(self::$PathToHelper . DS . 'mark_dailydeal.gif', 0, 0, 100, false);
            }
            $image->save($newImagePath);
        }

        // Set the product's image properties to the *partial* path to the new image file:
        $product->setData($attributeName, str_replace($baseMediaPath, '', $newImagePath));
    }

    public static function FindHtmlElement($html, $find, $multi = false)
    {
        $matches = array();
        $found = false;
        $end_offset = 0;
        while (!$found) {
            //$start_offset = strpos($html, "<{$find['tag_name']}", $end_offset);
            $preg_matches = array();
            $start_offset = preg_match("/\<({$find['tag_name']})/", substr($html, $end_offset), $preg_matches, PREG_OFFSET_CAPTURE) ? true : false;
            if ($start_offset) {
                $tag_name = $preg_matches[1][0];
                $start_offset = $preg_matches[1][1] + $end_offset - 1;
                $end_offset = strpos($html, ">", $start_offset);
                $tag = substr($html, $start_offset, $end_offset - $start_offset + 1);
                $found = true;

                // check attributes if given
                if (isset($find['attributes']) && is_array($find['attributes'])) {
                    // parse node attributes
                    $raw_attributes = trim(substr($tag, strlen($tag_name) + 1, -1));
                    $attributes = self::ParseNodeAttributes($raw_attributes);

                    // check condition attributes
                    foreach ($find['attributes'] as $name => $value) {
                        if (!isset($attributes[$name]) || !preg_match($value, $attributes[$name])) {
                            $found = false;
                            break;
                        }
                    }
                }
            }
            else
                break;

            if ($found) {
                // add a match if found
                $matches[] = array(
                    'tag' => $tag,
                    'tag_name' => $tag_name,
                    'attributes' => $attributes,
                    'raw_attributes' => $raw_attributes,
                    'start_offset' => $start_offset,
                    'end_offset' => $end_offset,
                );

                // break loop for single-mode
                if (!$multi)
                    break;

                // reset vars for multi-mode
                $found = false;
            }
        }

        // return array of matches for multi-mode or match (or null) for single-mode
        if ($multi)
            return $matches;
        else
            return array_shift($matches);
    }

    public static function ClearDBSchemaCaches()
    {
        $app = Mage::app();
        if ($app != null) {
            $cache = $app->getCache();
            if ($cache != null) {
                $cache->clean('matchingTag', array('DB_PDO_MYSQL_DDL'));
            }
        }
    }

    public static function ClearFPCCache($only_once = false)
    {
        if (!$only_once || Mage::registry("zizio/fpc_cleaned") == null) {
            Mage::app()->cleanCache('FPC');
            if ($only_once)
                Mage::register("zizio/fpc_cleaned", true);
        }
    }

    public static function NormalizeCurrency($price, $round = 2)
    {
        // normalize string represtentation
        if (is_string($price)) {
            $locale = localeconv();
            $dec = $locale['mon_decimal_point'];
            $sep = $locale['mon_thousands_sep'];
            $price = str_replace($sep, "", $price);
            $price = str_replace($dec, ".", $price);
            $price = 1 * $price;
        }

        // round
        if (!is_null($round))
            $round = round($price, $round);

        return $price;
    }

    /*
     * Use this when passing locale to zizio server.
     */

    public static function GetLocaleCode()
    {
        try {
            return Mage::app()->getLocale()->getLocaleCode();
        } catch (Exception $ex) {
            self::LogError($ex);
        }
        return "";
    }

    /*
     * Store default zizio query string arguments for current request
     */

    public static function StoreScriptUrlArgs($args = array())
    {
        self::$ScriptUrlArgs = self::$ScriptUrlArgs + $args;
    }

    /*
     * Get default zizio query string arguments for current request
     */

    public static function GetScriptUrlArgs()
    {
        return
                self::$ScriptUrlArgs +
                array(
                    'p' => self::GetPublisherId(),
                    'v' => self::$JS_VER,
                    'd' => self::$DESIGN_SKIN,
                    'l' => self::GetLocaleCode()
        );
    }

    /*
     * Leaving this here for reference only.
     */

    public static function GetLanguageCode()
    {
        try {
            return Mage::app()->getLocale()->getLocale()->getLanguage();
        } catch (Exception $ex) {
            self::LogError($ex);
        }
        return "";
    }

    public static function GetDefaultWebsiteName()
    {
        try {
            return Mage::app()->getDefaultStoreView()->getWebsite()->getName();
        } catch (Exception $ex) {
            self::LogError($ex);
        }
        return "";
    }

    public static function WriteWelcomeNotification()
    {
        $notification = array();
        $notification['url'] = "http://www.zizio.com/support/tutorial#welcome";
        $notification['title'] = "Thank you for having installed Zizio Social Daily Deals. To get started, click on <em>Zizio Social Daily Deals</em> in the <em>Promotions</em> menu.";
        $notification['description'] =
                "<p>Thank you for having installed the Zizio Social Daily Deals extension for Magento.</p>" .
                "<p>To get started, click on <em>Zizio Social Daily Deals</em> in the <em>Promotions</em> menu. You can learn more about the extension and how it can work for you in our <a href='" . $notification['url'] . "' target='_blank'>support website</a>. If you require further assistance, feel free to <a href='http://www.zizio.com/support/' target='_blank'>contact us.</a><p>" .
                "<p>-- The Zizio.com Team.</p>";
        $notification['severity'] = 4;
        $notification['is_read'] = 0;
        $notification['date_added'] = date('Y-m-d H:i:s');
        $notifications[] = $notification;
        // Save new notifications to DB:
        $inbox = Mage::getModel('adminNotification/inbox');
        $inbox->parse($notifications);
    }

    /*
     * Called only on fresh installation, after filling out registration form (/Zizio/Groupsale/controllers/Adminhtml/ZizioregController.php)
     */

    public static function InitializeSettings()
    {
        // Set config data
        self::SaveConfigValue(self::DESIGN_SKIN_PATH, "v2.0");        // Set skin to v2
        self::SaveConfigValue(self::DEALPAGE_CAPTION_PATH, "Daily Deal"); // Enable DealPage
        self::SaveConfigValue(self::DEALPAGE_ENABLED_PATH, "1");       // Enable DealPage
        self::SaveConfigValue(self::DEALPAGE_TOPLINK_PATH, "1");       // Show DealPage TopLink
        self::SaveConfigValue(self::DEALPAGE_URL_PATH, "dailydeal");      // Default DealPage URL
        self::SaveConfigValue(self::DEALPAGE_ROTATE_PATH, "0");           // Don't rotate dealpage featured deal
        self::SaveConfigValue(self::DEALPAGE_ONLY_PATH, "0");             // Show deals on product pages
        self::SaveConfigValue(self::ACCOUNT_NOTIFIED_PATH, "1");          // Disable Zizio Account notification
        self::SaveConfigValue(self::DBCHANGES_NOTIFIED_PATH, "1");        // Disable DB Changes notification
        // Refresh store config
        Mage::app()->getStore(null)->resetConfig();

        // Clear DB cache
        self::ClearDBSchemaCaches();
    }

    /*
     * Called by each:
     *  (1) adminhtml groupsale controller index action and
     *  (2) dealpage router
     * runs to ensure updated installation will have config values to preserve their usual functionality
     */

    public static function EnsureSettings()
    {
        // Set default (upgrade) config values
        if (Mage::getStoreConfig(self::PROMO_LIMIT_PATH) === null)
            self::SaveConfigValue(self::PROMO_LIMIT_PATH, 3);
        if (Mage::getStoreConfig(self::DEALPAGE_CAPTION_PATH) === null)
            self::SaveConfigValue(self::DEALPAGE_CAPTION_PATH, "Daily Deal");
        if (Mage::getStoreConfig(self::DEALPAGE_ENABLED_PATH) === null)
            self::SaveConfigValue(self::DEALPAGE_ENABLED_PATH, "0");
        if (Mage::getStoreConfig(self::DEALPAGE_TOPLINK_PATH) === null)
            self::SaveConfigValue(self::DEALPAGE_TOPLINK_PATH, "1");
        if (Mage::getStoreConfig(self::DEALPAGE_URL_PATH) === null)
            self::SaveConfigValue(self::DEALPAGE_URL_PATH, "dailydeal");
        if (Mage::getStoreConfig(self::DEALPAGE_ROTATE_PATH) === null)
            self::SaveConfigValue(self::DEALPAGE_ROTATE_PATH, "0");
        if (Mage::getStoreConfig(self::DEALPAGE_ONLY_PATH) === null)
            self::SaveConfigValue(self::DEALPAGE_ONLY_PATH, "0");
    }

    public static function GetAllCustomerGroupIds()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load();
        $customerGroupIds = array();
        $found = false;
        foreach ($customerGroups as $customerGroup) {
            $customerGroupIds[] = $customerGroup->getId();
            if ($customerGroup->getId() == 0)
                $found = true;
        }
        if (!$found)
            array_unshift($customerGroups, 0);
        return $customerGroupIds;
    }

    public static function BuildQS($args, $eq = "=", $sep = "&")
    {
        $enc_args = array();
        foreach ($args as $k => $v) {
            $enc_args[] = urlencode($k) . $eq . urlencode($v);
        }
        return implode($sep, $enc_args);
    }

    public static function GetNotificationBoxHtml($title, $content)
    {
        $body = "";
        foreach ($content as $item) {
            if ($item['type'] == "text") {
                $body .= "<p class='z-message-text'>{$item['text']}</p>";
            } else if ($item['type'] == "link") {
                $extra_attrs = "";
                if (isset($item['target']))
                    $extra_attrs = "target='{$item['target']}'";
                $body .= "<p class='z-read-more'><a href='{$item['link']}' style='float: right; margin-bottom: 12px;' {$extra_attrs}>{$item['caption']}</a></p>";
            }
        }

        $html = '
<style type="text/css">
	#z-message-popup-window-mask {
	    background-color: #EFEFEF;
	    bottom: 0;
	    height: 100%;
	    left: 0;
	    opacity: 0.5;
	    filter: alpha(opacity=50);
	    position: absolute;
	    right: 0;
	    top: 0;
	    width: 100%;
	    z-index: 9999;
	}

	.z-message-popup.z-show {
		top: 280px;
		display: block;
	}

	.z-message-popup {
		background: none repeat scroll 0 0 #F3BF8F;
		left: 50%;
		margin: 0 0 0 -203px;
		padding: 0 4px 4px;
		position: absolute;
		width: 407px;
		z-index: 99999;
		display: none;
	}

	.z-message-popup .z-message-popup-head:after, .z-message-popup .z-message-popup-content .z-message:after {
		clear: both;
		content: ".";
		display: block;
		font-size: 0;
		height: 0;
		line-height: 0;
		overflow: hidden;
	}

	.z-message-popup .z-message-popup-head {
		padding: 1px 0;
	}

	.z-message-popup .z-message-popup-head a {
		background: url("/skin/adminhtml/default/default/images/bkg_btn-close.gif") repeat-x scroll 0 50% #D97920 !important;
		border: 1px solid #EA7601;
		color: #FFFFFF;
		cursor: pointer;
		float: right;
		font: 12px/17px Arial,Helvetica,sans-serif;
		padding: 0 12px 0 7px;
		text-decoration: none !important;
		position: relative;
		width: 47px;
		height: 17px;
	}

	.z-message-popup .z-message-popup-head a i {
		position: absolute;
		left: 11px;
	}

	.z-message-popup .z-message-popup-head a span {
		position: absolute;
		left: 7px;
		padding-left: 19px;
		background: url("/skin/adminhtml/default/default/images/bkg_btn-close2.gif") no-repeat scroll 0 50% transparent;
	}

	.z-message-popup .z-message-popup-head h2 {
		color: #644F3B;
		font: bold 12px/19px Arial,Helvetica,sans-serif;
		margin: 0;
		padding: 0 10px;
	}

	.z-message-popup .z-message-popup-content {
		background: none repeat scroll 0 0 #FDF4EB;
		padding: 21px 21px 10px;
		width: 365px;
	}

	.z-message-popup .z-message-popup-content .z-message-icon {
		color: #659601;
		background-position: 50% 0;
		background-repeat: no-repeat;
		float: left;
		font-size: 10px;
		line-height: 12px;
		overflow: hidden;
		padding: 47px 0 0;
		text-align: center;
		text-transform: uppercase;
		width: 50px;
	}

	.z-message-popup .z-message-popup-content .z-read-more {
		margin: 7px 0 0;
		text-align: right;
	}

	.z-message-popup .z-message-popup-content .z-message-text {
		color: #644F3B;
		float: right;
		clear: right;
		/* min-height: 4.5em; */
		overflow: hidden;
		width: 295px;
	}

	.z-message p {
		text-align: left;
	}

	.z-clearfix {
		clear: both;
	}
</style>
<script type="text/javascript">
//<![CDATA[
    var messagePopupClosed = false;
    function openMessagePopup() {
        var height = $("html-body").getHeight();
        $("z-message-popup-window-mask").setStyle({"height":height+"px"});
        toggleSelectsUnderBlock($("z-message-popup-window-mask"), false);
        Element.show("z-message-popup-window-mask");
        $("z-message-popup-window").addClassName("z-show");
    }

    function closeMessagePopup() {
        toggleSelectsUnderBlock($("z-message-popup-window-mask"), true);
        Element.hide("z-message-popup-window-mask");
        $("z-message-popup-window").removeClassName("z-show");
        messagePopupClosed = true;

        if ($("message-popup-window-mask"))
			Element.hide("message-popup-window-mask");
		if ($$(".flash-window")[0])
			$$(".flash-window")[0].remove();
    }

    Event.observe(window, "keyup", function(evt) {
        if(messagePopupClosed) return;
        var code;
        if (evt.keyCode) code = evt.keyCode;
        else if (evt.which) code = evt.which;
        if (code == Event.KEY_ESC) {
            closeMessagePopup();
        }
    });

    if ($("z-message-popup-window"))
    	$("z-message-popup-window").remove();
    if ($("z-message-popup-window-mask"))
    	$("z-message-popup-window-mask").remove();
//]]>
</script>
<div id="z-message-popup-window-mask" style="display:none;"></div>
<div id="z-message-popup-window" class="z-message-popup">
    <div class="z-message-popup-head">
        <a href="#" onclick="closeMessagePopup(); return false;" title="close"><i>x</i><span>close</span></a>
        <h2>' . $title . '</h2>
    </div>
    <div class="z-message-popup-content">

        <div class="z-message">
            <span class="z-message-icon z-message-notice" style="background-image:url(http://widgets.magentocommerce.com/SEVERITY_NOTICE.gif);">notice</span>
			' . $body . '
        </div>

        <div class="z-clearfix"></div>

    </div>
</div>
<script type="text/javascript">
//<![CDATA[
	if ($("message-popup-window-mask"))
		Element.hide("message-popup-window-mask");
	if ($("message-popup-window"))
		Element.hide("message-popup-window");
	if ($$(".flash-window")[0])
		$$(".flash-window")[0].remove();
	openMessagePopup();
//]]>
</script>';

        return $html;
    }

    /*
     * Resources url builders
     */

    private static function BuildScriptUrl($relative_url, $args = array(), $html_encoded = true)
    {
        if ($html_encoded)
            list($eq, $sep) = array("=", "&amp;");
        else
            list($eq, $sep) = array("=", "&");

        $args = $args + self::GetScriptUrlArgs();
        $url = implode(array(self::$PROTOCOL, "://", self::$BASE_URL, self::$PORT, $relative_url, "?", self::BuildQS($args, $eq, $sep)));
        return $url;
    }

    ///////

    public static function GetAdminRegisterTermsUrl($args = array())
    {
        return self::BuildScriptUrl("/group/get_terms", $args);
    }

    public static function GetAdminProductPageScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/js/mage/admin_product.js", $args);
    }

    public static function GetAdminPromoPageScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/js/mage/admin_promo.js", $args);
    }

    public static function GetAdminRegisterScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/js/mage/admin_register.js", $args);
    }

    public static function GetZUtilsScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/js/zutils.js", $args);
    }

    public static function GetProductPageScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/res/mage/product", $args);
    }

    public static function GetPostGroupsale_ProductPageScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/res/mage/product_post_sale", $args);
    }

    public static function GetPromptGsScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/res/mage/prompt_group", $args);
    }

    public static function GetPromoScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/res/mage/promo_group", $args);
    }

    public static function GetSuccessPageScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/res/mage/success", $args);
    }

    public static function GetRegisterUrl($args = array())
    {
        return self::BuildScriptUrl("/group/mage_reg", $args, false);
    }

    public static function GetDealpageScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/res/mage/dealpage", $args);
    }

    public static function GetAdminGridScriptUrl($args = array())
    {
        return self::BuildScriptUrl("/js/mage/admin_grid.js", $args);
    }

    public static function GetZizioLoginUrl()
    {
        return "https://" . self::$ZIZIO_ACCOUNT_URL;
    }

    public static function GetZizioAttachUrl()
    {
        $pub_id = self::GetPublisherId();
        $data = self::ToBase64(self::json_encode(array('attach' => true)));

        $args = array(
            'd' => $data,
            'pub_id' => $pub_id
        );
        $args['_token'] = self::ToBase64(self::GetToken($args, true));

        $url = implode(array("https://", self::$ZIZIO_ACCOUNT_URL, "/attach?", self::BuildQS($args, "=", "&amp;")));
        return $url;
    }

    public static function GetDealsRSSUrl($type = "active")
    {
        $url = implode(array("http://", self::$ZIZIO_RSS_URL, "/", self::GetPublisherId(), "/{$type}.xml"));
        return $url;
    }

    /**
     * Return template for setTemplate function in layout to set root (1column,2column or 3column)
     *
     * @return string
     */
    public function chooseColumnLayout()
    {
    	//echo 'yess';
        $columnNumber = Mage::getStoreConfig('dailydeal/general/deallayout');
        switch ($columnNumber) {
            case 'empty':
                return 'page/empty.phtml';
                break;
            case 'one_column':
                return 'page/1column.phtml';
                break;
            case 'two_columns_left':
                return 'page/2columns-left.phtml';
                break;
            case 'two_columns_right':
                return 'page/2columns-right.phtml';
                break;
            case 'three_columns':
                return 'page/3columns.phtml';
                break;
            default:
                return 'page/3columns.phtml';
                break;
        }
    }

    public function getRewriteUrl($id_path)
    {
        $url = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);
        if ($url) {
            return $url->getRequestPath();
        } else {
            return "";
        }
    }

    public static function reIndexArray(array &$array)
    {
        $temp = $array;
        $array = array();
        foreach ($temp as $key => $value) {
            $array[] = $value;
        }
    }

    /**
     * Get url follow http or https
     */
    public function getUrlHttp($url = '', $rewrite_url = false){
        if($rewrite_url == true){
            $url = $this->getRewriteUrl($url);
        }else{
            
        }
        $link = Mage::getUrl($url,array('_secure'=>Mage::app()->getFrontController()->getRequest()->isSecure()));
        return $link;
    }
    
    /**
     * Render html countdown for category
     */
    public static function categoryDealInfo($product)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('dailydeal/deal')
                ->setData('product', $product)
                ->setTemplate('glace_dailydeal/catalog/product/countdown.phtml');
        return $block->renderView();
    }

    /**
     * Render html countdown for product detail
     */
    public static function productDealInfo($product)
    {

        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('dailydeal/deal')
                ->setData('product', $product)
                ->setTemplate('glace_dailydeal/catalog/product/view_countdown.phtml');
        return $block->renderView();
    }

    /**
     * Override in layout for 6 product type
     * Render html countdown for product detail ( default in layout )
     */
    public static function renderProductDetailCoundownEach($product)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('dailydeal/deal')
                ->setData('product', $product)
                ->setTemplate('glace_dailydeal/catalog/product/view_countdown.phtml');
        return $block->renderView();
    }
    
    public static function renderDealQtyOnProductPage($qty){
        $value = self::getConfigDealQtyOnProductPage();
        $search = array('{{qty}}');
        $replace = array($qty);
        $html = str_replace($search, $replace, $value);
        echo $html;
    }
    
    public static function renderDealQtyOnCatalogPage($qty){
        $value = self::getConfigDealQtyOnCatalogPage();
        $search = array('{{qty}}');
        $replace = array($qty);
        $html = str_replace($search, $replace, $value);
        echo $html;
    }
    
    /**
     * Render html countdown for category
     */
    public static function showImage($product)
    {
        if (!Glace_Dailydeal_Helper_Toolasiaconnect::getInstance()->isModuleOutputEnabled()) {
            return;
        }

        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('dailydeal/deal')
                ->setData('product', $product)
                ->setTemplate('glace_dailydeal/catalog/product/show_image.phtml');
        return $block->renderView();
    }

    public static function getConfigDisplayQuantity($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/general/showqty', $store_id);
    }

    public static function getConfigSchemeColor($store_id = "")
    {
        return '#' . Mage::getStoreConfig('dailydeal/general/schemecolor', $store_id);
    }

    public static function getConfigCountdownColor($store_id = "")
    {
        return '#' . Mage::getStoreConfig('dailydeal/general/countdowncolor', $store_id);
    }

    public static function getConfigHighlightColor($store_id = "")
    {
        return '#' . Mage::getStoreConfig('dailydeal/general/highlight_color', $store_id);
    }

    public static function getConfigSendMailAdminNotification($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/global_variable/send_mail_admin_notification', $store_id);
    }

    public static function getConfigAllowSendAdminMail($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/admin_notification/notify_admin', $store_id);
    }

    public static function getConfigAdminMail($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/admin_notification/admin_email', $store_id);
    }

    public static function getConfigTemplateIdNoDeal($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/global_variable/template_id_no_deal', $store_id);
    }

    public static function getConfigIsShowImageCatalogList($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/general/catalog_list_show_image', $store_id);
    }

    public static function getUrlImageCatalogList($store_id = "")
    {
        if(self::getConfigLabelImage() == ''){
            $url = Mage::getBaseUrl('media'). 'glace_dailydeal/' . 'images/sale.png';
        }else{
            $url = Mage::getBaseUrl('media'). 'glace_dailydeal/' . self::getConfigLabelImage($store_id);
        }
        
        return $url;
    }

    public static function getConfigTodayDealShowDetail($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/general/today_deal_show_detail', $store_id);
    }

    public static function getConfigOption($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/general/product_view_show_countdown', $store_id);
    }
    
    public static function getConfigSortBy($store_id = "")
    {
        return Mage::getStoreConfig('dailydeal/general/sort_by', $store_id);
    }
    
    public static function getConfigDealQtyOnProductPage($store_id = ""){
        return Mage::getStoreConfig('dailydeal/general/deal_qty_on_product_page', $store_id);
    }
    
    public static function getConfigDealQtyOnCatalogPage($store_id = ""){
        return Mage::getStoreConfig('dailydeal/general/deal_qty_on_catalog_page', $store_id);
    }
    
    public static function getConfigLabelImage($store_id = ""){
        return Mage::getStoreConfig('dailydeal/general/label_image', $store_id);
    }

    public static function setConfigSendMailAdminNotification($value = "")
    {
        $path = 'dailydeal/global_variable/send_mail_admin_notification';
        Mage::getModel('core/config_data')
                ->load($path, 'path')
                ->setValue($value)
                ->setPath($path)
                ->save();
    }
	
    const MYNAME = "Glace_Dailydeal";	
    const MYCONFIG = "dailydeal/general/enabled";
    
    public function myConfig(){
        return self::MYCONFIG;
    }
	
    function disableConfig()
    {
        Mage::getSingleton('core/config')->saveConfig($this->myConfig(),0); 			
        Mage::getModel('core/config')->saveConfig("advanced/modules_disable_output/".self::MYNAME,1);	
        Mage::getConfig()->reinit();
    }
}

Glace_Dailydeal_Helper_Data::StaticConstructor();
