<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
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
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace OpenSearchServerSearch\Controller\Admin;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Model\Base\ProductQuery;
use OpenSearchServerSearch\Form\ConfigurationForm;
use OpenSearchServerSearch\Model\OpensearchserverConfigQuery;
use OpenSearchServerSearch\OpenSearchServerSearch;
use OpenSearchServerSearch\Helper\OpenSearchServerSearchHelper;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

/**
 * Class OpenSearchServerSearchAdminController
 * @package OpenSearchServerSearch\Controller\Admin
 * @author Alexandre Toyer <alexandre.toyer@open-search-server.com>
 */
class OpenSearchServerSearchAdminController extends BaseAdminController
{
    protected $basePath;

    public function __construct()
    {
        $this->basePath = OpenSearchServerSearch::getBasePath();
    }

    /**
     * return the absolute path to OpenSearchServer module
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    public function defaultAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, array(), AccessManager::VIEW)) {
            return $response;
        }
    
        return $this->renderTemplate();
    }
    
    /**
     * @return mixed an HTTP response, or
     */
    public function configureAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'OpenSearchServerSearch', AccessManager::UPDATE)) {
            return $response;
        }

        // Initialize the potential error message, and the potential exception
        $error_msg = $ex = null;

        // Create the Form from the request
        $configurationForm = new ConfigurationForm($this->getRequest());

        try {
            // Check the form against constraints violations
            $form = $this->validateForm($configurationForm, "POST");

            // Get the form field values
            $data = $form->getData();

            foreach ($data as $name => $value) {
                if (is_array($value)) {
                    $value = implode(';', $value);
                }
                OpensearchserverConfigQuery::set($name, $value);
            }
        
            //get handle to work with the API
            $oss_api = OpenSearchServerSearchHelper::getHandler();
            
            //check if index exists, if not, creates it
            $index = OpensearchserverConfigQuery::read('index_name');
            $indexCreated = false;
            $request = new \OpenSearchServer\Index\Exists();
            $request->index($index);
            $response = $oss_api->submit($request);
            //index doesn't exist, create it
            if (!$response->isSuccess()) {
                $this->createIndex($index);
                $indexCreated = true;
            }
            
            //check if Analyzer "PriceAnalyzer" exists, create it if it doesn't
            $analyzerName = 'PriceAnalyzer';
            $request = new \OpenSearchServer\Analyzer\Get();
            $request->index($index)
                    ->name($analyzerName);
            $response = $oss_api->submit($request);
            if (!$response->isSuccess()) {
                $this->createAnalyzer($index, $analyzerName);
            }
            
            //if index has just been created, create its schema
            if ($indexCreated) {
                $this->createSchema($index);
            }
            
            //check if query template exists, if not, creates it
            $queryTemplate = OpensearchserverConfigQuery::read('query_template');
            $request = new \OpenSearchServer\SearchTemplate\Get();
            $request->index($index)
                    ->name($queryTemplate);
            $response = $oss_api->submit($request);
            if (!$response->isSuccess()) {
                $this->createQueryTemplate($index, $queryTemplate);
            }
            
            // Log configuration modification
            $this->adminLogAppend(
                "opensearchserversearch.configuration.message",
                AccessManager::UPDATE,
                "OpenSearchServer configuration updated"
            );

            // Redirect to the success URL,
            if ($this->getRequest()->get('save_mode') == 'stay') {
                // If we have to stay on the same page, redisplay the configuration page/
                $route = '/admin/module/OpenSearchServerSearch';
            } else {
                // If we have to close the page, go back to the module back-office page.
                $route = '/admin/modules';
            }

            
            //$this->getRequest()->getSession()->getFlashBag()->add('notice', $this->getTranslator()->trans('Settings have been saved.'));
            $this->getRequest()->getSession()->getFlashBag()->add('oss', $this->getTranslator()->trans('Settings have been saved.', [], OpenSearchServerSearch::MODULE_DOMAIN));

            return RedirectResponse::create(URL::getInstance()->absoluteUrl($route));
        } catch (FormValidationException $ex) {
            // Form cannot be validated. Create the error message using
            // the BaseAdminController helper method.
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
             $error_msg = $ex->getMessage();
        }

        // At this point, the form has errors, and should be redisplayed. We don not redirect,
        // just redisplay the same template.
        // Setup the Form error context, to make error information available in the template.
        $this->setupFormErrorContext(
            $this->getTranslator()->trans("OpenSearchServer configuration", [], OpenSearchServerSearch::MODULE_DOMAIN),
            $error_msg,
            $configurationForm,
            $ex
        );

        
        // Do not redirect at this point, or the error context will be lost.
        // Just redisplay the current template.
        return $this->renderTemplate();
    }

    
    /**
     * Called by actions buttons in configuration page
     */
    public function adminActionsAction()
    {
        $adminAction = $this->getRequest()->request->get('adminAction');
        if (!empty($adminAction) && is_callable(array($this, $adminAction.'Action'), true, $callable_name)) {
            $method = $adminAction.'Action';
            return $this->$method();
        }
    }
    
    /**
	 * Index all products
     */
    private function indexAllAction()
    {
        $products = ProductQuery::create()->findByVisible(1);
        $count = 0;
        foreach ($products as $product) {
            OpenSearchServerSearchHelper::indexProduct($product);
            $count++;
        }

        $this->getRequest()->getSession()->getFlashBag()->add('oss', $this->getTranslator()->trans('%count products have been indexed.', array('%count' => $count), OpenSearchServerSearch::MODULE_DOMAIN));
        return $this->redirectToHome();
    }
    
    /**
	 * Delete all products
     */
    private function deleteAllAction()
    {
        // Get name of index and handler to work with OSS API
        $index = OpensearchserverConfigQuery::read('index_name');
        $oss_api = OpenSearchServerSearchHelper::getHandler();
        
        //delete every documents from index
        $request = new \OpenSearchServer\Document\DeleteByQuery();
        $request->index($index)
                ->query('id:[* TO *]');
        $response = $oss_api->submit($request);
        

        $this->getRequest()->getSession()->getFlashBag()->add('oss', $this->getTranslator()->trans('All data have been deleted.', [], OpenSearchServerSearch::MODULE_DOMAIN));
        return $this->redirectToHome();
    }
    
    /**
     * Create an index in OpenSearchServer's instance
     * @param string $index Name of the index to create
     */
    private function createIndex($index)
    {
        //get handle to work with the API
        $oss_api = OpenSearchServerSearchHelper::getHandler();
        
        //create index
        $request = new \OpenSearchServer\Index\Create();
        $request->index($index);
        $response = $oss_api->submit($request);
        return $response->isSuccess();
    }

    
    /**
     * Create schema in an index
     * @param string $index Name of the index to use
     */
    private function createSchema($index)
    {
        //get handle to work with the API
        $oss_api = OpenSearchServerSearchHelper::getHandler();
   
        //create schema
        $request = new \OpenSearchServer\Field\CreateBulk(null, file_get_contents($this->getBasePath() . '/Config/oss_schema.json'));
        $request->index($index);
        $response = $oss_api->submit($request);
        
        //set default and unique field
        $request = new \OpenSearchServer\Field\SetDefaultUnique();
        $request->index($index)
                ->defaultField('title')
                ->uniqueField('uniqueId');
        $response = $oss_api->submit($request);
        return $response->isSuccess();
    }
    
    /**
     * Create a template of query in an index
     * @param string $index Name of the index to work with
     * @param string $queryTemplate Name of the template to create
     */
    private function createQueryTemplate($index, $queryTemplate)
    {
        //get handle to work with the API
        $oss_api = OpenSearchServerSearchHelper::getHandler();
        
        $request = new \OpenSearchServer\Search\Field\Put(null, file_get_contents($this->getBasePath() . '/Config/oss_querytemplate.json'));
        $request->index($index)
                ->template($queryTemplate);
        $response = $oss_api->submit($request);
    }
    

    
    /**
     * Create an analyzer
     * @param string $index Name of the index to create
     */
    private function createAnalyzer($index, $analyzer)
    {
        //get handle to work with the API
        $oss_api = OpenSearchServerSearchHelper::getHandler();
        
        if (is_file($this->getBasePath() . '/Config/oss_analyzer_'.$analyzer.'.json') && is_readable($this->getBasePath() . '/Config/oss_analyzer_'.$analyzer.'.json')) {
            $request = new \OpenSearchServer\Analyzer\Create(null, file_get_contents($this->getBasePath() . '/Config/oss_analyzer_'.$analyzer.'.json'));
            $request->index($index)
                    ->name($analyzer);
            $response = $oss_api->submit($request);
            return $response->isSuccess();
        }
        return false;
    }
    
    protected function renderTemplate()
    {
        $flash = $this->getRequest()->getSession()->getFlashBag()->get('oss');
        $flashMessage = isset($flash[0]) ? $this->getRequest()->getSession()->getFlashBag()->get('oss')[0] : null;
        return $this->render(
            'module-configure',
            array(
                'module_code' => 'OpenSearchServerSearch',
                'flash_message' => $flashMessage
            )
        );
    }

    /**
     * redirect to OpenSearchServer admin home
     *
     * @return \Symfony\Component\HttpFoundation\Response|static
     */
    protected function redirectToHome()
    {
        return RedirectResponse::create(URL::getInstance()->absoluteUrl('/admin/module/OpenSearchServerSearch'));
    }
}
