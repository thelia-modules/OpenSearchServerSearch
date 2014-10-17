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

namespace OpenSearchServerSearch\Listener;

use Thelia\Log\Tlog;

use Thelia\Core\Event\TheliaEvents;

use Thelia\Core\Event\Product\ProductEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenSearchServerSearch\Model\OpensearchserverConfigQuery;
use OpenSearchServerSearch\Helper\OpenSearchServerSearchHelper;

/**
 */
class OpenSearchServerSearchProductListener implements EventSubscriberInterface
{

    public function indexProduct(ProductEvent $event) {
        //var_dump($event->getProduct()->getCurrentTranslation());
        //var_dump($event->getProduct());exit;

        /************************************
         * Get name of index and handler to work with OSS API
         ************************************/
        $index = OpensearchserverConfigQuery::read('index_name');
        $oss_api = OpenSearchServerSearchHelper::getHandler();
        
        /************************************
         * Create/update document
         ************************************/
        $product = $event->getProduct();
        $document = new \OpenSearchServer\Document\Document();
        //TODO : complete handling of language
        switch($product->getCurrentTranslation()->getLocale()) {
            case 'fr_FR':
                $document->lang(\OpenSearchServer\Request::LANG_FR);
                break;
            case 'en_EN':
            case 'en_US':
                $document->lang(\OpenSearchServer\Request::LANG_EN);
                break;
        }
        $document->field('id', OpenSearchServerSearchHelper::makeProductUniqueId($product))
                 ->field('title', $product->getTitle())
                 ->field('locale', $product->getCurrentTranslation()->getLocale())
                 ->field('description', $product->getDescription())
                 ->field('chapo', $product->getChapo())
                 ->field('reference', $product->getRef());
                
        /************************************
         * Send request to OSS
         ************************************/ 
        $request = new \OpenSearchServer\Document\Put();
        $request->index($index)
                ->addDocument($document);
        $response = $oss_api->submit($request);
        
        //var_dump($oss_api->getLastRequest());
        //var_dump($response);
        //exit;
    }
    
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::PRODUCT_UPDATE => ['indexProduct', 128],
            TheliaEvents::PRODUCT_CREATE => ['indexProduct', 128]
        );
    }
}

