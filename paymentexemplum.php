<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

defined('_CAN_LOAD_FILES_') or exit;

class PaymentExemplum extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'paymentexemplum';
        $this->tab = 'payments_gateways';
        $this->version = '0.0.1';
        $this->author = 'TechDesign';
        $this->controllers = ['ExecutePayment'];
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_
        ];
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        parent::__construct();
        $this->displayName = $this->getTranslator()->trans(
            'PayPal Checkout',
            [],
            'Modules.Paypalcheckout.Admin'
        );
        $this->description = $this->getTranslator()->trans(
            'Accept payments via PayPal.',
            [],
            'Modules.Paypalcheckout.Admin'
        );
    }

    public function install()
    {
        $this->installModuleOnXML();
        Configuration::updateValue('PAYMENTEXEMPLUM_SANDBOX', true);
        Configuration::updateValue('PAYMENTEXEMPLUM_CLIENT_ID', null);
        Configuration::updateValue('PAYMENTEXEMPLUM_CLIENT_SECRET', null);
        Configuration::updateValue('PAYMENTEXEMPLUM_SANDBOX_CLIENT_ID', null);
        Configuration::updateValue('PAYMENTEXEMPLUM_SANDBOX_CLIENT_SECRET', null);
        return Db::getInstance()->execute(
            preg_replace(
                '/prefix_/',
                _DB_PREFIX_,
                Tools::file_get_contents(dirname(__FILE__).'/database/create-tables.sql')
            )
        ) &&
        parent::install() &&
        $this->registerHook('paymentOptions') &&
        $this->registerHook('displayAdminOrder');
    }

    public function uninstall()
    {
        $this->uninstallModuleOnXML();
        Configuration::deleteByName('PAYMENTEXEMPLUM_SANDBOX');
        Configuration::deleteByName('PAYMENTEXEMPLUM_CLIENT_ID');
        Configuration::deleteByName('PAYMENTEXEMPLUM_CLIENT_SECRET');
        Configuration::deleteByName('PAYMENTEXEMPLUM_SANDBOX_CLIENT_ID');
        Configuration::deleteByName('PAYMENTEXEMPLUM_SANDBOX_CLIENT_SECRET');
        return
            Db::getInstance()->execute(
                preg_replace(
                    '/prefix_/',
                    _DB_PREFIX_,
                    Tools::file_get_contents(dirname(__FILE__) . '/database/remove-tables.sql')
                )
            ) &&
            parent::uninstall();
    }

    protected function installModuleOnXML()
    {
        if (file_exists(_PS_ROOT_DIR_.Module::CACHE_FILE_TAB_MODULES_LIST)) {
            $xml_tab_modules_list = @simplexml_load_file(_PS_ROOT_DIR_.Module::CACHE_FILE_TAB_MODULES_LIST);
            foreach ($xml_tab_modules_list->tab as $tab) {
                if ((string) $tab['class_name'] === 'AdminPayment') {
                    $module = $tab->addChild('module');
                    $module->addAttribute('name', $this->name);
                    $module->addAttribute('position', $tab->count() + 1);
                    break;
                }
            }
            file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_TAB_MODULES_LIST, $xml_tab_modules_list->asXML());
        }
    }

    protected function uninstallModuleOnXML()
    {
        if (file_exists(_PS_ROOT_DIR_.Module::CACHE_FILE_TAB_MODULES_LIST)) {
            $xml = simplexml_load_file(_PS_ROOT_DIR_.Module::CACHE_FILE_TAB_MODULES_LIST);
            $tab_count = 0;
            foreach ($xml->tab as $tab) {
                if ((string) $tab['class_name'] === 'AdminPayment') {
                    $module_count = 0;
                    foreach ($tab->module as $module) {
                        if ((string) $module['name'] === $this->name) {
                            unset($xml->tab[$tab_count]->module[$module_count]);
                            break;
                        }
                        $module_count++;
                    }
                }
                $tab_count++;
            }
            file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_TAB_MODULES_LIST, $xml->asXML());
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPaymentExemplumSettings')
        );
    }
}
