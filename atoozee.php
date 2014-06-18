<?php
//[[atoozee? &parent=`17` &sortbyTV=`overview_show_how` &includeTVs=`overview_show_how`]]

//define variables
$parent = (!empty($parent) ? $parent : $modx->resource->get('id')); //parent to get children from
$ignore = (!empty($ignore) ? $ignore : '');
$sortby = (!empty($sortby) ? $sortby : 'pagetitle');
$sortbyTV = (!empty($sortbyTV) ? $sortbyTV : '');
$sortdir = (!empty($sortdir) ? $sortdir : 'ASC');
$tplIndex = (!empty($tplIndex) ? $tplIndex : 'tplIndex');
$tplList = (!empty($tplList) ? $tplList : 'tplList');
$tplListItem = (!empty($tplListItem) ? $tplListItem : 'tplListItem');
$includeTVs = (!empty($includeTVs) ? $includeTVs : '');

//define used variables
$eerste = '';
$index_array = array();

//retrieve children objects
$parentObject = $modx->getObject('modResource', $parent);

$criteria = $modx->newQuery('modResource');
$criteria->where(array(
   'published' => 1,
   'deleted' => 0
));
//$criteria->sortby($sortby,$sortdir);
$children = $parentObject->getMany('Children',$criteria);

//set to array
foreach($children as $child){

	//verdere data ophalen
	if(!empty($sortbyTV)){
		$title = $child->getTVValue($sortbyTV);
	}else{
		$title = $child->get($sortby);
	}

	//eerste letter uitrekenen
	$eerste = substr($title, 0,1);

	//pushen in array met desbetreffende eerste letter
	$index_array[$eerste][] = $child->get('id');
	//sorteren op alfabet
	ksort($index_array);
}

//outputten naar placeholders
//ophalen content
foreach($index_array as $letter=>$documents){
	
	$indexarray = array(
		'letter' => $letter,
		'anchor' => 'a'.$letter
	);

	$ph_index .= $modx->getChunk($tplIndex,$indexarray);

	$ph_listItems = '';
	$ph_listItemsArray = array();
	foreach($documents as $document){
		$resource = $modx->getObject('modResource', $document);

		$item_array = array(
			'id' => $resource->get('id'),
			'pagetitle' => $resource->get('pagetitle'),
			'longtitle' => $resource->get('longtitle'),
			'introtext' => $resource->get('introtext'),
			'menutitle' => $resource->get('menutitle')
		);

		//eventuele TV's ophalen die opgegeven zijn
		if(!empty($includeTVs)){
			$includeTV_array = explode(',',$includeTVs);
			foreach($includeTV_array as $tvname){
				$tvvalue = $resource->getTVValue($tvname);
				$item_array[$tvname] = $tvvalue;
			}
		}
		if(!empty($sortbyTV)){
			$title = $resource->getTVValue($sortbyTV);
		}else{
			$title = $resource->get($sortby);
		}

		$ph_listItemsArray[$title] = $modx->getChunk($tplListItem,$item_array);
		ksort($ph_listItemsArray);
		$ph_listItems = implode('',$ph_listItemsArray);
	}

	$list_array = array(
		'letter' => $letter,
		'items' => $ph_listItems
	);
	$ph_list .= $modx->getChunk($tplList,$list_array);
}

//ouput to placeholder
$modx->setPlaceholder('index',$ph_index);
$modx->setPlaceholder('list',$ph_list);

return;