<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class Whatsapp extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'whatsapp';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'jh-design.cz';
        $this->need_instance = 0;

        
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Whatsapp');
        $this->description = $this->l('Modul pro kontakt Whatsapp.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    
    public function install()
    {
        Configuration::updateValue('WHATSAPP_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('WHATSAPP_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitWhatsappModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWhatsappModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-phone"></i>',
                        'desc' => $this->l('Zadejte platné telefonní číslo'),
                        'name' => 'WHATSAPP_ACCOUNT_EMAIL',
                        'label' => $this->l('Whatsapp telefonní číslo'),
                   
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'WHATSAPP_LIVE_MODE' => Configuration::get('WHATSAPP_LIVE_MODE', true),
            'WHATSAPP_ACCOUNT_EMAIL' => Configuration::get('WHATSAPP_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'WHATSAPP_ACCOUNT_PASSWORD' => Configuration::get('WHATSAPP_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayFooter()
    {
        $this->context->smarty->assign('phonex',Configuration::get('WHATSAPP_ACCOUNT_EMAIL'));
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/front/whatsapp.tpl');
        return $output;
    }
}
