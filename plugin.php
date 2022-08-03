<?php

class PrivateModeNgPlugin extends Plugin
{

    public function init()
    {
        $this->dbFields = array(
            'enable' => true,
            'message' => 'Private Access Only',
            'redirect' => DOMAIN_ADMIN,
            'private-all' => true,
            'private-category' => '',
            'pmngOnSidebar' => 'show'
        );
    }

    public function form()
    {
        global $L;

        /*
        * Enable private mode
        */
        $html .= PHP_EOL . '<div class="form-group">' . PHP_EOL;
        $html .= '<label style="font-size:1.2rem">' . $L->get('label-enable-private-mode') . '</label>' . PHP_EOL;
        $html .= '<select name="enable">' . PHP_EOL;
        $html .= '<option value="true" ' . ($this->getValue('enable') === true ? 'selected' : '') . '>' . $L->get('enabled') . '</option>' . PHP_EOL;
        $html .= '<option value="false" ' . ($this->getValue('enable') === false ? 'selected' : '') . '>' . $L->get('disabled') . '</option>' . PHP_EOL;
        $html .= '</select>' . PHP_EOL;
        $html .= '<small class="text-muted">' . $L->get('help-enable-private-mode') . '</small>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        /*
        * Message
        */
        $html .= PHP_EOL . '<div class="form-group">' . PHP_EOL;
        $html .= '<label style="font-size:1.2rem">' . $L->get('label-message') . '</label>' . PHP_EOL;
        $html .= '<input name="message" id="jsmessage" type="text" value="' . $this->getValue('message') . '">' . PHP_EOL;
        $html .= '<small class="text-muted">' . $L->get('help-message') . '</small>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        /*
        * Redirect url
        */
        $html .= PHP_EOL . '<div class="form-group">' . PHP_EOL;
        $html .= '<label style="font-size:1.2rem">' . $L->get('label-redirect') . '</label>' . PHP_EOL;
        $html .= '<input name="redirect" type="text" value="' . $this->getValue('redirect') . '">' . PHP_EOL;
        $html .= '<small class="text-muted">' . $L->get('help-redirect') . '</small>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        /*
        * Private all
        */
        $html .= PHP_EOL . '<div class="form-group">' . PHP_EOL;
        $html .= '<label style="font-size:1.2rem">' . $L->get('label-private-all') . '</label>' . PHP_EOL;
        $html .= '<select name="private-all">';
        $html .= '<option value="true" ' . ($this->getValue('private-all') === true ? 'selected' : '') . '>' . $L->get('enabled') . '</option>';
        $html .= '<option value="false" ' . ($this->getValue('private-all') === false ? 'selected' : '') . '>' . $L->get('disabled') . '</option>';
        $html .= '</select>' . PHP_EOL;
        $html .= '<small class="text-muted">' . $L->get('help-private-all') . '</small>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        /*
        * Private category
        */
        $html .= PHP_EOL . '<div class="form-group">' . PHP_EOL;
        $html .= '<label style="font-size:1.2rem">' . $L->get('label-private-category') . '</label>' . PHP_EOL;
        if($cats = getCategories()):
            $html .= '<select name="private-category">' . PHP_EOL;
            foreach($cats as $objCat):
                $html .= '<option value="'.$objCat->key().'" ' . ($this->getValue('private-category') === $objCat->key() ? 'selected' : '') . '>'.$objCat->name().'</option>' . PHP_EOL;
            endforeach;
            $html .= '</select>' . PHP_EOL;
            $html .= '<small class="text-muted">' . $L->get('help-private-category') . '</small>' . PHP_EOL;
        else:
            $html .= '<div class="alert alert-warning" role="alert">' . $L->get('empty-categories') . '</div>' . PHP_EOL;
        endif;
        $html .= '</div>' . PHP_EOL;

        /*
        * Show or hide plugin menu item on sidebar
        */
        $html .= PHP_EOL . '<div class="form-group">' . PHP_EOL;
        $html .= '<label for="pmngOnSidebar" style="font-size:1.2rem">' . $L->get('label-show-sidebar') . '</label>' . PHP_EOL;
        $html .= '<select id="pmngOnSidebar" name="pmngOnSidebar">' . PHP_EOL;
        $html .= '<option value="show" ' . ($this->getValue('pmngOnSidebar') === 'show' ? 'selected' : '') . '>' . $L->g('Show') . '</option>' . PHP_EOL;
        $html .= '<option value="hide" ' . ($this->getValue('pmngOnSidebar') === 'hide' ? 'selected' : '') . '>' . $L->g('Hide') . '</option>' . PHP_EOL;
        $html .= '</select>' . PHP_EOL;
        $html .= '<small class="text-muted">' . $L->get('help-show-sidebar') . '</small>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        return $html;
    }

    public function adminSidebar()
    {
        global $L;

        $badge='Off';
        if($this->getValue('enable')) $badge='On';

        $html = '';
        if ($this->getValue('pmngOnSidebar') && $this->getValue('pmngOnSidebar') === 'show') {
            $html  = '<a id="" class="nav-link" href="' . HTML_PATH_ADMIN_ROOT . 'configure-plugin/' . $this->className() . '" title="">Private Mode NG<span class="badge badge-warning badge-pill">'.$badge.'</span></a>';
        }

        return $html;
    }

    public function beforeAll()
    {

        if ($this->getValue('enable')) {

            $login = new Login();

            /**
             * 302 Redirect to admin if not logged in and set private to all.
             */
            if (! $login->isLogged() && $this->getValue('private-all') === true) {
                Alert::set($this->getValue('message'));
                Redirect::url($this->getValue('redirect'));
            }

            /**
             * Remove pages with specified category if not logged in (all other).
             */
            if (! $login->isLogged()) {
                global $pages;

                foreach ($pages->db as $key=>$page) {
                    if($page['category'] == $this->getValue('private-category')) {
                        unset($pages->db[$key]);
                    }
                }
            }
        }
    }

    // This hook is loaded when the pages are already set
    public function beforeSiteLoad() {

        if ($this->getValue('enable')) {

            global $content;

            $login = new Login();
            if (! $login->isLogged()) {
                foreach ($content as $key=>$page) {
                    if(strtolower($page->category()) == $this->getValue('private-category')){
                        unset($content[$key]);
                    }
                }
            }
        }
    }

}
