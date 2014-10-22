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

namespace OpenSearchServerSearch\Controller\Front;


use Front\Front;
use OpenSearchServerSearch\Model\OpensearchserverConfigQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Front\BaseFrontController;
use OpenSearchServerSearch\Form\ConfigurationForm;
use OpenSearchServerSearch\OpenSearchServerSearch;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Template\ParserInterface;

/**
 * Class OpenSearchServerSearchFrontController
 * @package OpenSearchServerSearch\Controller\Front
 * @author Alexandre Toyer <alexandre.toyer@open-search-server.com>
 */
class OpenSearchServerSearchFrontController extends BaseFrontController
{
    
    public function search() {
        //get keywords
        $request = $this->getRequest();
        $keywords = $request->query->get('q', null);
        
        //get locale
        $locale = $request->getSession()->getLang()->getLocale();
        //fix bug ?
        if($locale == 'fr_FR') $locale = 'fr_Fr';
        
        switch($locale) {
            case 'fr_Fr':
            case 'fr_FR':
                $lang = \OpenSearchServer\Request::LANG_FR;
                break;
            case 'en_EN':
            case 'en_US':
                $lang = \OpenSearchServer\Request::LANG_EN;
                break;
        }
        
        $index = OpensearchserverConfigQuery::read('index_name');
        $queryTemplate = OpensearchserverConfigQuery::read('query_template');

        //create handler for requests
        $oss_api = \OpenSearchServerSearch\Helper\OpenSearchServerSearchHelper::getHandler();
        
        //create search request
        $request = new \OpenSearchServer\Search\Field\Search();
        $request->index($index)
                ->template($queryTemplate)
                //set lang of keywords
                ->lang($lang)
                //filter to get only documents with current locale
                ->filterField('locale', $locale)
                ->enableLog()
                ->query($keywords);
        $response = $oss_api->submit($request);
                
        $ids = array();
        foreach($response->getResults() as $result) {
            $ids[] = $result->getField('id');
        }
        
        //display results      
        return $this->render('oss_results', array(
        	'module_code' => 'OpenSearchServerSearch',
            'keywords' => $keywords,
            'results' => $response->getResults(),
            'ids' => implode(',', $ids)
        
        ));
    }
    
}