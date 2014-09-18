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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Front\BaseFrontController;
use OpenSearchServerSearch\Form\ConfigurationForm;
use OpenSearchServerSearch\Model\OpenSearchServerConfigQuery;
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
        return $this->render('search', array('module_code' => 'OpenSearchServerSearch'));
    }
    
}