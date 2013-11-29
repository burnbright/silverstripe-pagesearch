# SilverStripe Page Search

A simple search replacement for the default SilverStripe search functionality.

Searches will be performed on all Text,HTMLText,Varchar, and HTMLVarchar fields for the selected pagetype.

Pagetype can be specifically set with url vairalble, eg: `?pagetype=BlogEntry`.

## Limitations

 * Currently only tested with MySQLDatabase.
 * Does not search on relations. Use a better engine like SOLR for this.

## Usage

In your `Page_Controller` class:

```php

	public function SearchForm() {
		$searchText = ($this->request && $this->request->requestVar('Search')) ?
						$this->request->requestVar('Search') : 'Search';
		$form = new Form(
			$this, 'SearchForm',
			new FieldList(
				TextField::create('Search', false)
					->setAttribute("Placeholder", $searchText)
			),
			new FieldList(
				FormAction::create('results', 'Go')
			)
		);
		$form->disableSecurityToken();
		$form->setFormMethod('GET');
		$form->setTemplate('SearchForm');

		return $form;
	}

	function results($data, $form, $request) { 

		$search = PageSearch::create($request);

		$data = array(
			'Content' => '',
			'Results' => $search->results(),
			'Query' => $search->getQuery(),
			'Title' => _t('SearchForm.SearchResults', 'Search Results')
		); 
		return $this->owner->customise($data)->renderWith(array('Page_results', 'Page'));
	}

```