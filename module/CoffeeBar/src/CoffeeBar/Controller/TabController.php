<?php

namespace CoffeeBar\Controller ;

use CoffeeBar\Entity\TabStory\OrderModel;
use CoffeeBar\Exception\TabAlreadyOpened;
use Zend\Mvc\Controller\AbstractActionController;

class TabController extends AbstractActionController
{
    public function openAction()
    {
        $form = $this->serviceLocator->get('OpenTabForm') ;
        $request = $this->getRequest() ;

        if($request->isPost()) {
            $form->setData($request->getPost()) ;
            
            try {
                $form->isValid() ;
                $openTab = $form->getObject() ;
                return $this->redirect()->toRoute('tab/order', array('id' => $openTab->getTableNumber()));
            } catch (TabAlreadyOpened $ex) {
                $this->flashMessenger()->addErrorMessage($ex->getMessage());
                return $this->redirect()->toRoute('tab/open');
            }
        }

        $result['form'] = $form ;
        return array('result' => $result) ;
    }
    
    public function orderAction()
    {
        // utiliser la clé déclarée dans le Service Manager (classe Module)
        $form = $this->serviceLocator->get('PlaceOrderForm') ;
        $request = $this->getRequest() ;

        // vérifier si on connait le numéro de la table pour laquelle on passe commande
        if ($id = (int) $this->params()->fromRoute('id')) {
            $form->get('id')->setValue($id) ;
        // sinon, vérifier si le formulaire a été posté
        } elseif($request->isPost()) {
            $form->setData($request->getPost()) ;
            if($form->isValid()) {
                $orderModel = $form->getObject() ;
                $tableNumber = $orderModel->getId() ;
                $openTabs = $this->serviceLocator->get('OpenTabs') ;
                $placeOrder = $this->serviceLocator->get('PlaceOrderCommand') ;
                $items = $this->assignOrderedItems($orderModel) ;
                $placeOrder->placeOrder($openTabs->tabIdForTable($tableNumber), $items) ;
                return $this->redirect()->toRoute('tab/status', array('id' => $tableNumber));
            }
        // si on ne sait pas pour quelle table on va passer commande, retourner à la page 'Ouvrir une commande'
        } else {
            return $this->redirect()->toRoute('tab/open');
        }
        
        $result['form'] = $form ;
        return array('result' => $result) ;
    }

    public function closeAction()
    {
        $openTabs = $this->serviceLocator->get('OpenTabs') ;

        $form = $this->serviceLocator->get('CloseTabForm') ;
        $request = $this->getRequest() ;
        $id = (int) $this->params()->fromRoute('id') ;

        // vérifier si on connait le numéro de la table pour laquelle on passe commande
        if (isset($id)) {
            $status = $openTabs->invoiceForTable($id) ;

            if($status->hasUnservedItems())
            {
                $this->flashMessenger()->addErrorMessage('Il reste des éléments commandés pour cette table');
                return $this->redirect()->toRoute('tab/status', array('id' => $id));
            }

            $form->get('id')->setValue($id) ;
        // sinon, vérifier si le formulaire a été posté
        } elseif($request->isPost()) {
            $form->setData($request->getPost()) ;
            
            if($form->isValid())
            {
                $closeTab = $form->getObject() ;
                var_dump($closeTab) ;
//                return $this->redirect()->toRoute('tab/order', array('id' => $openTab->getTableNumber()));
//            } catch (TabAlreadyOpened $ex) {
//                $this->flashMessenger()->addErrorMessage($ex->getMessage());
//                return $this->redirect()->toRoute('tab/open');
            }
        // si on ne sait pas pour quelle table on va passer commande, retourner à la page 'Ouvrir une commande'
        } else {
            return $this->redirect()->toRoute('tab/opened');
        }

        $result['status'] = $status ;
        $result['form'] = $form ;
        return array('result' => $result) ;
    }
    
    public function listOpenedAction()
    {
        $cache = $this->serviceLocator->get('TabCache') ;
        $openTabs = $cache->getOpenTabs() ;
        return array('result' => $openTabs) ;
    }
    
    public function statusAction()
    {
        $openTabs = $this->serviceLocator->get('OpenTabs') ;
        $status = $openTabs->statusForTable($this->params()->fromRoute('id')) ;
        return array('result' => $status) ;
    }
    
    public function servedAction()
    {
        $request = $this->getRequest() ;    
        if($request->isPost()) {
            $id = $request->getPost()->get('tableNumber') ;

            if(!is_array($request->getPost()->get('served'))) {
                $this->flashMessenger()->addErrorMessage('Aucun plat ou boisson n\'a été choisi pour servir');
                return $this->redirect()->toRoute('tab/status', array('id' => $id));
            } 
            $menuNumbers = $this->extractMenuNumber($request->getPost()->get('served')) ;

            $this->markDrinksServed($id, $menuNumbers) ;
            $this->markFoodServed($id, $menuNumbers) ;
        }
        return $this->redirect()->toRoute('tab/status', array('id' => $id)) ;
    }
    
    protected function assignOrderedItems(OrderModel $model)
    {
        $items = $this->serviceLocator->get('OrderedItems') ;
        $menu = $this->serviceLocator->get('CoffeeBarEntity\MenuItems') ;
        foreach($model->getItems() as $item)
        {
            for($i = 0; $i < $item->getNumber(); $i++)
            {
                $orderedItem = clone $this->serviceLocator->get('OrderedItem') ;
                $orderedItem->setId($item->getId()) ;
                $orderedItem->setDescription($menu->getById($item->getId())->getDescription()) ;
                $orderedItem->setPrice($menu->getById($item->getId())->getPrice()) ;
                $orderedItem->setIsDrink($menu->getById($item->getId())->getIsDrink()) ;
                $items->offsetSet(NULL, $orderedItem) ;
            }
        }
        return $items ;
    }
    
    protected function extractMenuNumber(array $markServedItems)
    {
        $array = array() ;
        foreach($markServedItems as $value)
        {
            $groups = explode('_', $value) ;
            $array[] = $groups[2] ;
        }
        return $array ;
    }
    
    protected function markDrinksServed($id, array $menuNumbers)
    {
        $menu = $this->serviceLocator->get('CoffeeBarEntity\MenuItems') ;
        $openTabs = $this->serviceLocator->get('OpenTabs') ;
        $tabId = $openTabs->tabIdForTable($id) ;
        
        $drinks = array() ;
        foreach($menuNumbers as $nb)
        {
            if($menu->getById($nb)->getIsDrink())
            {
                $drinks[] = $nb ; 
            }
        }
        
        if(!empty($drinks))
        {
            $markServed = $this->serviceLocator->get('MarkDrinksServedCommand') ;
            $markServed->markServed($tabId, $drinks) ;
        }
    }
    
    protected function markFoodServed($id, array $menuNumbers)
    {
        $menu = $this->serviceLocator->get('CoffeeBarEntity\MenuItems') ;
        $openTabs = $this->serviceLocator->get('OpenTabs') ;
        $tabId = $openTabs->tabIdForTable($id) ;
        
        $food = array() ;
        foreach($menuNumbers as $nb)
        {
            if(!$menu->getById($nb)->getIsDrink())
            {
                $food[] = $nb ; 
            }
        }

        if(!empty($food))
        {
            $markServed = $this->serviceLocator->get('MarkFoodServedCommand') ;
            $markServed->markServed($tabId, $food) ;
        }
    }
}