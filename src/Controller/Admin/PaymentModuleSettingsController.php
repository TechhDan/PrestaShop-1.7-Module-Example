<?php
/**
* 2019 TechDesign
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@techdesign.io so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade TechDesign to newer
* versions in the future. If you wish to customize TechDesign for your
* needs please refer to http://techdesign.io for more information.
*
*  @author    TechDesign <admin@techdesign.io>
*  @copyright 2019 TechDesign
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of TechDesign
*/

namespace PrestaShop\TechDesign\PaymentModule\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShop\TechDesign\PaymentModule\Form\FormType;
use Symfony\Component\HttpFoundation\Request;
use Configuration;

class PaymentModuleSettingsController extends FrameworkBundleAdminController
{
    public function configAction(Request $request)
    {
        $form = $this->get('prestashop.module.paymentmodule.service')->getFactory()->createForm();
        $form->setProduction(
            $this->get('prestashop.module.paymentmodule.service')
                ->getFactory()
                ->createProductionConfiguration()
        );
        $form->setSandbox(
            $this->get('prestashop.module.paymentmodule.service')
                ->getFactory()
                ->createSandboxConfiguration()
        );
        $form->setSettings(
            $this->get('prestashop.module.paymentmodule.service')
                ->getFactory()
                ->createSettingsConfiguration()
        );
        $form = $this->createForm(FormType::class, $form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form->getClickedButton()->getName()) {
                case 'production_save':
                    Configuration::updateValue(
                        'PAYMENTMODULE_CLIENT_ID',
                        $form->getData()->getProduction()->getClientId()
                    );
                    Configuration::updateValue(
                        'PAYMENTMODULE_SECRET',
                        $form->getData()->getProduction()->getSecret()
                    );
                    Configuration::updateValue(
                        'PAYMENTMODULE_VERIFIED',
                        $this->get('prestashop.module.paymentmodule.service')->configurationCredentialsVerified(
                            $form->getData()->getProduction()
                        ) ? 'true' : 'false'
                    );
                    $default_view = 'production';
                    break;
                case 'sandbox_save':
                    Configuration::updateValue(
                        'PAYMENTMODULE_SANDBOX_CLIENT_ID',
                        $form->getData()->getSandbox()->getClientId()
                    );
                    Configuration::updateValue(
                        'PAYMENTMODULE_SANDBOX_SECRET',
                        $form->getData()->getSandbox()->getSecret()
                    );
                    Configuration::updateValue(
                        'PAYMENTMODULE_SANDBOX_VERIFIED',
                        $this->get('prestashop.module.paymentmodule.service')->configurationCredentialsVerified(
                            $form->getData()->getSandbox()
                        ) ? 'true' : 'false'
                    );
                    $default_view = 'sandbox';
                    break;
                case 'settings_save':
                    Configuration::updateValue(
                        'PAYMENTMODULE_SANDBOX',
                        $form->getData()->getSettings()->getSandbox() ? 'true' : 'false'
                    );
                    $default_view = 'configuration';
                    break;
            }
        }
        return $this->render('@Modules/paymentmodule/views/templates/admin/config.html.twig', [
            'form' => $form->createView(),
            'default_view' => $default_view ?? 'production',
            'sandbox_verified' => Configuration::get('PAYMENTMODULE_SANDBOX_VERIFIED') === 'true' ? true : false,
            'production_verified' => Configuration::get('PAYMENTMODULE_VERIFIED') === 'true' ? true : false
        ]);
    }
}
