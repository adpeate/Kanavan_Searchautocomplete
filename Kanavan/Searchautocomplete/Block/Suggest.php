<?php
class Kanavan_Searchautocomplete_Block_Suggest extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getSearchautocomplete()     
     { 
        if (!$this->hasData('searchautocomplete')) {
            $this->setData('searchautocomplete', Mage::registry('searchautocomplete'));
        }
        return $this->getData('searchautocomplete');
     }

     public function getSuggestProducts()     
     {


        $query = Mage::helper('catalogsearch')->getQuery();
        $query->setStoreId(Mage::app()->getStore()->getId());

                if ($query->getRedirect()){
                    $query->save();
                }
                else {
                    $query->prepare();
                }
            Mage::helper('catalogsearch')->checkNotes();


            //$results=$query->getResultCollection();//->setPageSize(5);

		if(Mage::getStoreConfig('catalog/frontend/flat_catalog_product') == 1 || Mage::getStoreConfig('catalog/search/search/type') == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_FULLTEXT || Mage::getStoreConfig('catalog/search/search/type') == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE ) {
			$resHelper = Mage::getResourceHelper('core');
			$likeOptions = array('position' => 'any');
			
			//$searchTerms = $this->filterSearchTerms($query->getQueryText());
			$searchTerms = $query->getQueryText();
			$searchTermVariants = $this->getVariantSearchTerms($query->getQueryText());
			
			$whereOps = array();
			$whereOps[] = $resHelper->getCILike('search.data_index', $searchTerms, $likeOptions);
			$whereOps[] = 'search.data_index LIKE "%'.$searchTerms.'%"';
			
			foreach($searchTermVariants as $stv){
				//$whereOps[] = $resHelper->getCILike('search.data_index', $stv, $likeOptions);
				$whereOps[] = 'search.data_index LIKE "%'.$stv.'%"';
			}
			
			$whereSql = $this->joinWhereOr($whereOps);
			
			$catalogsearchTable = Mage::getSingleton('core/resource')->getTableName('catalogsearch/fulltext');
			$results = Mage::getResourceModel('catalogsearch/search_collection');
			$results->getSelect()
				->join(array('search' => $catalogsearchTable), 'e.entity_id=search.product_id', array())
				->where($whereSql)				
				->where('search.store_id='.(int)Mage::app()->getStore()->getId());
			if(Mage::helper('core')->isModuleEnabled('MageRage_Search')){
				$whereOps = array();
				$whereOps[] = $resHelper->getCILike('search.mageragesearch_data_index', $searchTerms, $likeOptions);
				$whereOps[] = 'search.mageragesearch_data_index LIKE "%'.$searchTerms.'%"';
				
				foreach($searchTermVariants as $stv){
					//$whereOps[] = $resHelper->getCILike('search.mageragesearch_data_index', $stv, $likeOptions);
					$whereOps[] = 'search.mageragesearch_data_index LIKE "%'.$stv.'%"';
				}
				
				$whereSql = $this->joinWhereOr($whereOps);
				
				$results->getSelect()
					->where($whereSql);
					
			}
		} else {

			$results=Mage::getResourceModel('catalogsearch/search_collection')->addSearchFilter(Mage::app()->getRequest()->getParam('q'));
		}

        $results->addAttributeToFilter('visibility', array('neq' => 1));


        if(Mage::getStoreConfig('searchautocomplete/preview/number_product'))
        {
            $results->setPageSize(Mage::getStoreConfig('searchautocomplete/preview/number_product'));
        }
        else
        {
            $results->setPageSize(5);
        }
        $results->addAttributeToSelect('description');
        $results->addAttributeToSelect('name');
        $results->addAttributeToSelect('thumbnail');
        $results->addAttributeToSelect('small_image');
        $results->addAttributeToSelect('url_key');


        return $results;
    }
    
    private function getVariantSearchTerms($terms){
		
		$allChars = str_split($terms);	
		$separatorKeys = array_keys($allChars, ' ');
		$return = array();
		
		foreach($separatorKeys as $sk){
			$tmpChars = $allChars;
			unset($tmpChars[$sk]);
			$return[] = implode('', $tmpChars);
		}
		
		//also try the whole term with no spaces
		$return[] = str_replace(' ', '', $terms);
		
		return $return;		
	}
	
	private function filterSearchTerms($terms){
		$tmp = explode(' ', $terms);
		foreach($tmp as $k => $v){
			if(strlen($v) <= 1){
				unset($tmp[$k]);
			}
		}
		return implode(' ', $tmp);
	}
    
    private function joinWhereOr($terms){
		$sql = "";
		$sqlTmp = array();
		foreach($terms as $t){
			$sqlTmp[] = "({$t})";
		}
		return implode(' OR ', $sqlTmp);
	}
	
     public function enabledSuggest()     
     {
        return Mage::getStoreConfig('searchautocomplete/suggest/enable');
      }

     public function enabledPreview()     
     {
        return Mage::getStoreConfig('searchautocomplete/preview/enable');
     }

     public function getImageWidth()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/image_width');
     }

     public function getImageHeight()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/image_height');
     }
     public function getEffect()
     {
        return Mage::getStoreConfig('searchautocomplete/settings/effect');
     }

     public function getPreviewBackground()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/background');
     }

     public function getSuggestBackground()
     {
        return Mage::getStoreConfig('searchautocomplete/suggest/background');
     }

     public function getSuggestColor()
     {
        return Mage::getStoreConfig('searchautocomplete/suggest/suggest_color');
     }

     public function getSuggestCountColor()
     {
        return Mage::getStoreConfig('searchautocomplete/suggest/count_color');
     }

     public function getBorderColor()
     {
        return Mage::getStoreConfig('searchautocomplete/settings/border_color');
     }

     public function getBorderWidth()
     {
        return Mage::getStoreConfig('searchautocomplete/settings/border_width');
     }

     public function isShowImage()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/show_image');
     }

     public function isShowName()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/show_name');
     }
     public function getProductNameColor()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/pro_title_color');
     }

     public function getProductDescriptionColor()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/pro_description_color');
     }


     public function isShowDescription()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/show_description');
     }

     public function getNumDescriptionChar()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/num_char_description');
     }


     public function getImageBorderWidth()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/image_border_width');
     }
     public function getImageBorderColor()
     {
        return Mage::getStoreConfig('searchautocomplete/preview/image_border_color');
     }

     public function getHoverBackground()
     {
        return Mage::getStoreConfig('searchautocomplete/settings/hover_background');
     }

}
