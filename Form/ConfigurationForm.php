<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace OpenSearchServerSearch\Form;

use OpenSearchServerSearch\Model\OpensearchserverConfigQuery;
use OpenSearchServerSearch\OpenSearchServerSearch;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Base\ModuleQuery;
use Thelia\Model\Module;

/**
 * OpenSearchServer configuration form
 *
 * @author Alexandre Toyer <alexandre.toyer@open-search-server.com>
 */
class ConfigurationForm extends BaseForm
{
    protected function trans($str, $params = [])
    {
        return Translator::getInstance()->trans($str, $params, OpenSearchServerSearch::MODULE_DOMAIN);
    }

    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'hostname',
                'text',
                array(
                    'constraints' => array(new NotBlank()),
                    'required' => true,
                    'label' => $this->trans('OpenSearchServer instance URL'),
                    'data' => OpensearchserverConfigQuery::read('hostname', 'http://localhost:9090'),
                    'label_attr' => array(
                        'for' => 'hostname',
                        'help' => $this->trans('URL to access your OpenSearchServer instance. Please include port information.')
                    )
                )
            )
            ->add(
                'login',
                'text',
                array(
                    'constraints' => array(new NotBlank()),
                    'required' => true,
                    'label' => $this->trans('Login'),
                    'data' => OpensearchserverConfigQuery::read('login', ''),
                    'label_attr' => array(
                        'for' => 'login'
                    )
                )
            )
            ->add(
                'apikey',
                'text',
                array(
                    'constraints' => array(new NotBlank()),
                    'required' => true,
                    'label' => $this->trans('API Key'),
                    'data' => OpensearchserverConfigQuery::read('apikey', ''),
                    'label_attr' => array(
                        'for' => 'apikey'
                    )
                )
            )
            ->add(
                'index_name',
                'text',
                array(
                    'constraints' => array(new NotBlank()),
                    'required' => true,
                    'label' => $this->trans('Index to use'),
                    'data' => OpensearchserverConfigQuery::read('index_name', ''),
                    'label_attr' => array(
                        'for' => 'index_name',
                        'help' => $this->trans('Index will be automatically created if it does not already exist.')
                    )
                )
            )
            ->add(
                'query_template',
                'text',
                array(
                    'constraints' => array(new NotBlank()),
                    'required' => true,
                    'label' => $this->trans('Query template'),
                    'data' => OpensearchserverConfigQuery::read('query_template', 'search'),
                    'label_attr' => array(
                        'for' => 'query_template',
                        'help' => $this->trans('Query template to use for searching products. If it does not already exists in index it will be automatically created from a basic template.')
                    )
                )
            )
            
            ->add(
                'enable_search',
                'checkbox',
                array(
                    'required' => false,
                    'label' => $this->trans('Enable search with OpenSearchServer'),
                    'data' => (boolean)OpensearchserverConfigQuery::read('enable_search', ''),
                    'label_attr' => array(
                        'for' => 'enable_search',
                        'help' => $this->trans('Enable this option when configuration is completed.')
                    )
                )
            );
    }

    public function getName()
    {
        return 'opensearchserversearch_configuration_form';
    }
}
