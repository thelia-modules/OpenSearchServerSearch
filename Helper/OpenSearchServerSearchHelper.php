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

namespace OpenSearchServerSearch\Helper;


use Thelia\Model\ProductPriceQuery;

use OpenSearchServerSearch\Model\OpensearchserverConfigQuery;

/**
 * Class OpenSearchServerSearchHelper
 * @package OpenSearchServerSearch\Helper
 * @author Alexandre Toyer <alexandre.toyer@open-search-server.com>
 */
class OpenSearchServerSearchHelper
{

    public static function getHandler() {
        $url = OpensearchserverConfigQuery::read('hostname');
        $login = OpensearchserverConfigQuery::read('login');
        $apiKey = OpensearchserverConfigQuery::read('apikey');

        //create handler for requests
        $ossApi = new \OpenSearchServer\Handler(array('url' => $url, 'key' => $apiKey, 'login' => $login ));

        return $ossApi;
    }

    public static function makeProductUniqueId($locale, \Thelia\Model\Base\Product $product) {
        //concatenate locale + ref
        return $locale.'_'.$product->getRef();
    }

    public static function indexProduct(\Thelia\Model\Base\Product $product) {
        /************************************
         * Get name of index and handler to work with OSS API
         ************************************/
        $index = OpensearchserverConfigQuery::read('index_name');
        $oss_api = OpenSearchServerSearchHelper::getHandler();

        /************************************
         * Create/update document
         ************************************/
        //get price from first combination SaleElement
        $collSaleElements  = $product->getProductSaleElementss();
        $infos = $collSaleElements->getFirst()->toArray();
        $price  = ProductPriceQuery::create()
                        ->findOneByProductSaleElementsId($infos['Id'])
                        ->toArray();

        //create one document by translation
        $translations = $product->getProductI18ns();
        //Prepare request for OSS
        $request = new \OpenSearchServer\Document\Put();
        $request->index($index);
        foreach ($translations as $translation) {
            $document = new \OpenSearchServer\Document\Document();
            $productI18nInfos = $translation->toArray();

            //TODO : complete list of languages
            switch($productI18nInfos['Locale']) {
                case 'fr_Fr':
                case 'fr_FR':
                    $document->lang(\OpenSearchServer\Request::LANG_FR);
                    break;
                case 'en_EN':
                case 'en_US':
                    $document->lang(\OpenSearchServer\Request::LANG_EN);
                    break;
            }
            
            $document   ->field('uniqueId', OpenSearchServerSearchHelper::makeProductUniqueId($productI18nInfos['Locale'], $product))
                        ->field('id', $product->getId())
                        ->field('title', $productI18nInfos['Title'])
                        ->field('locale',  $productI18nInfos['Locale'])
                        ->field('description', $productI18nInfos['Description'])
                        ->field('chapo', $productI18nInfos['Chapo'])
                        ->field('price', self::formatPrice($price['Price']))
                        ->field('currency', $price['CurrencyId'])
                        ->field('reference', $product->getRef());
    
            $request->addDocument($document);
        }
        $response = $oss_api->submit($request);
        
        return $response->isSuccess();
        //var_dump($oss_api->getLastRequest());
        //var_dump($response);
        //exit;
    }
    
    public static function formatPrice($price) {
        return str_replace(' ', '', $price);
    }

}