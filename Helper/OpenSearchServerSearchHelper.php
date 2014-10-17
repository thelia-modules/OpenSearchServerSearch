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
    
    public static function makeProductUniqueId(\Thelia\Model\Base\Product $product) {
        //concatenate locale + ref
        return $product->getCurrentTranslation()->getLocale().'_'.$product->getRef();
    }
    
}