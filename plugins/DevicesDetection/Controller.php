<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

use DeviceDetector\DeviceDetector;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\DevicesDetection\Reports\GetBrand;
use Piwik\Plugins\DevicesDetection\Reports\GetBrowserEngines;
use Piwik\Plugins\DevicesDetection\Reports\GetBrowsers;
use Piwik\Plugins\DevicesDetection\Reports\GetModel;
use Piwik\Plugins\DevicesDetection\Reports\GetOsVersions;
use Piwik\Plugins\DevicesDetection\Reports\GetType;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        $view = new View('@DevicesDetection/index');
        $view->deviceTypes = $view->deviceModels = $view->deviceBrands = $view->osReport = $view->browserReport = "blank";
        $view->deviceTypes = $this->renderReport(new GetType());
        $view->deviceBrands = $this->renderReport(new GetBrand());
        $view->deviceModels = $this->renderReport(new GetModel());
        $view->osReport = $this->renderReport(new GetOsVersions());
        $view->browserReport = $this->renderReport(new GetBrowsers());
        $view->browserEngineReport = $this->renderReport(new GetBrowserEngines());
        return $view->render();
    }

    public function deviceDetection()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $view = new View('@DevicesDetection/detection');
        $this->setBasicVariablesView($view);
        ControllerAdmin::setBasicVariablesAdminView($view);

        $userAgent = Common::getRequestVar('ua', $_SERVER['HTTP_USER_AGENT'], 'string');

        $uaParser = new DeviceDetector($userAgent);
        $uaParser->parse();

        $view->userAgent           = $userAgent;
        $view->browser_name        = $uaParser->getClient('name');
        $view->browser_short_name  = $uaParser->getClient('short_name');
        $view->browser_version     = $uaParser->getClient('version');
        $view->browser_logo        = getBrowserLogo($uaParser->getClient('short_name'));
        $view->browser_family      = \DeviceDetector\Parser\Client\Browser::getBrowserFamily($uaParser->getClient('short_name'));
        $view->browser_family_logo = getBrowserFamilyLogo($view->browser_family);
        $view->os_name             = $uaParser->getOs('name');
        $view->os_logo             = getOsLogo($uaParser->getOs('short_name'));
        $view->os_short_name       = $uaParser->getOs('short_name');
        $view->os_family           = \DeviceDetector\Parser\OperatingSystem::getOsFamily($uaParser->getOs('short_name'));
        $view->os_family_logo      = getOsFamilyLogo($view->os_family);
        $view->os_version          = $uaParser->getOs('version');
        $view->device_type         = getDeviceTypeLabel($uaParser->getDeviceName());
        $view->device_type_logo    = getDeviceTypeLogo($uaParser->getDeviceName());
        $view->device_model        = $uaParser->getModel();
        $view->device_brand        = getDeviceBrandLabel($uaParser->getBrand());
        $view->device_brand_logo   = getBrandLogo($uaParser->getBrand());

        return $view->render();
    }

    public function showList()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $view = new View('@DevicesDetection/list');

        $type = Common::getRequestVar('type', 'brands', 'string');

        $list = array();

        switch ($type) {
            case 'brands':
                $availableBrands = \DeviceDetector\Parser\Device\DeviceParserAbstract::$deviceBrands;

                foreach ($availableBrands as $short => $name) {
                    if ($name != 'Unknown') {
                        $list[$name] = getBrandLogo($name);
                    }
                }
                break;

            case 'browsers':
                $availableBrowsers = \DeviceDetector\Parser\Client\Browser::getAvailableBrowsers();

                foreach ($availableBrowsers as $short => $name) {
                    $list[$name] = getBrowserLogo($short);
                }
                break;

            case 'browserfamilies':
                $availableBrowserFamilies = \DeviceDetector\Parser\Client\Browser::getAvailableBrowserFamilies();

                foreach ($availableBrowserFamilies as $name => $browsers) {
                    $list[$name] = getBrowserFamilyLogo($name);
                }
                break;

            case 'os':
                $availableOSs = \DeviceDetector\Parser\OperatingSystem::getAvailableOperatingSystems();

                foreach ($availableOSs as $short => $name) {
                    $list[$name] = getOsLogo($short);
                }
                break;

            case 'osfamilies':
                $osFamilies = \DeviceDetector\Parser\OperatingSystem::getAvailableOperatingSystemFamilies();

                foreach ($osFamilies as $name => $oss) {
                    $list[$name] = getOsFamilyLogo($name);
                }
                break;

            case 'devicetypes':
                $deviceTypes = \DeviceDetector\Parser\Device\DeviceParserAbstract::getAvailableDeviceTypes();

                foreach ($deviceTypes as $name => $id) {
                    $list[$name] = getDeviceTypeLogo($name);
                }
                break;
        }

        $view->itemList = $list;

        return $view->render();
    }
}
