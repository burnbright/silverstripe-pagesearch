<?php

/**
 * A simple class for running a search from url get vars
 */
class PageSearch extends Object{

	protected $fields;
	protected $request, $query;
	protected $pagetype = "Page";
	protected $mode = ' IN BOOLEAN MODE'; //TODO allow different modes

	function __construct(SS_HTTPRequest $request){
		$this->request = $request;
		$this->query = trim($this->request->requestVar('Search'));
	}

	function getQuery(){
		return Convert::raw2xml($this->query);
	}

	function getPageType(){
		return $this->request->getVar('pagetype') &&
			class_exists($this->request->getVar('pagetype')) &&
			singleton($this->request->getVar('pagetype')) instanceof SiteTree
			? $this->request->getVar('pagetype') 
			: $this->pagetype;
	}

	function setPageType($classname){
		$this->pagetype = $classname;
		return $this;
	}

	function setFields($fields){
		$this->fields = $fields;
		return $this;
	}

	function results(){
		$keyword = Convert::raw2sql($this->query);
		$keywordHTML = htmlentities($keyword, ENT_NOQUOTES, 'UTF-8');
		$fields = implode(",",$this->fields ? $this->fields : $this->getSearchableFields());
		$siteTreeMatch = "MATCH( $fields ) AGAINST ('$keyword'$this->mode) 
						+ MATCH( $fields ) AGAINST ('$keywordHTML'$this->mode)";
		$pagetype = $this->getPageType();
		$results = DataList::create($pagetype)
				->filter("ShowInSearch",1)
				->where($siteTreeMatch);
		$results->sort(array(
			'Relevance' => 'DESC',
			'Title' => 'ASC'
		));

		$this->getSearchableFields();

		return $results;
	}

	/**
	 * Get string-based dbfields for the selected pagetype
	 * @return array searchable fields
	 */
	protected function getSearchableFields(){
		$allfields = singleton($this->getPageType())->db();
		$dbfieldtypes = array('Text','HTMLText','Varchar','HTMLVarchar');
		$fields = array();
		foreach($allfields as $field => $type){
			foreach($dbfieldtypes as $dbtype){
				if(strpos($type, $dbtype) !== false){ //strpos is used becase of fields like Varchar(123)
					$fields[$field] = $field;
				}
			}
		}
		return $fields;
	}

}
