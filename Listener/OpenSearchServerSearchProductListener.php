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

use Thelia\Core\Event\File\FileCreateOrUpdateEvent;

use Thelia\Core\Event\Product\ProductUpdateEvent;
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
    public function indexProduct(ProductUpdateEvent $event)
    {
        OpenSearchServerSearchHelper::indexProduct($event->getProduct());
    }

    public function deleteProduct(ProductEvent $event)
    {
        OpenSearchServerSearchHelper::deleteProduct($event->getProduct());
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
            TheliaEvents::PRODUCT_UPDATE => ['indexProduct', 0],
            TheliaEvents::PRODUCT_CREATE => ['indexProduct', 0],
            //TheliaEvents::IMAGE_SAVE => ['updateImage', 0],
            TheliaEvents::PRODUCT_UPDATE_PRODUCT_SALE_ELEMENT=> ['indexProduct', 0],
            TheliaEvents::AFTER_DELETEPRODUCT => ['deleteProduct', 0]
        );
    }
}
