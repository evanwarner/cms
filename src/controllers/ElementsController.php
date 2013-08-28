<?php
namespace Craft;

/**
 * Element actions
 */
class ElementsController extends BaseController
{
	/**
	 * Renders and returns the body of an ElementSelectorModal.
	 */
	public function actionGetModalBody()
	{
		$this->requireAjaxRequest();

		$showSources = craft()->request->getParam('sources');
		$context = craft()->request->getParam('context');
		$elementType = $this->_getElementType();
		$sources = $elementType->getSources($context);

		if (is_array($showSources))
		{
			foreach (array_keys($sources) as $source)
			{
				if (!in_array($source, $showSources))
				{
					unset($sources[$source]);
				}
			}
		}

		$this->renderTemplate('_elements/modalbody', array(
			'sources'   => $sources
		));
	}

	/**
	 * Renders and returns the list of elements in an ElementIndex.
	 */
	public function actionGetElements()
	{
		$mode = craft()->request->getParam('mode', 'index');
		$elementType = $this->_getElementType();
		$source = craft()->request->getParam('source');
		$viewState = craft()->request->getParam('viewState');
		$disabledElementIds = craft()->request->getParam('disabledElementIds');

		$baseCriteria = craft()->request->getPost('criteria');
		$criteria = craft()->elements->getCriteria($elementType->getClassHandle(), $baseCriteria);

		if ($source)
		{
			$criteria->source = $source;
		}

		if ($search = craft()->request->getParam('search'))
		{
			$criteria->search = $search;
		}

		if ($offset = craft()->request->getParam('offset'))
		{
			$criteria->offset = $offset;
		}

		$containerVars = array(
			'state' => $viewState
		);

		$elementVars = array(
			'mode'               => $mode,
			'elementType'        => new ElementTypeVariable($elementType),
			'disabledElementIds' => $disabledElementIds,
		);

		if ($viewState['mode'] == 'table')
		{
			// Make sure the attribute is actually allowed
			$tableAttributes = $elementType->defineTableAttributes($source);

			// Ordering by an attribute?
			if (!empty($viewState['order']))
			{
				foreach ($tableAttributes as $attribute)
				{
					if ($attribute['attribute'] == $viewState['order'])
					{
						$criteria->order = $viewState['order'].' '.$viewState['sort'];
						break;
					}
				}
			}

			$containerVars['attributes'] = $tableAttributes;
			$elementVars['attributes'] = $tableAttributes;
		}

		// Find the elements!
		$elementVars['elements'] = $criteria->find();

		$viewFolder = '_elements/'.$viewState['mode'].'view/';

		if (!$criteria->offset)
		{
			$elementContainerHtml = $this->renderTemplate($viewFolder.'container', $containerVars, true);
		}
		else
		{
			$elementContainerHtml = null;
		}

		$elementDataHtml = $this->renderTemplate($viewFolder.'elements', $elementVars, true);
		$totalVisible = $criteria->offset + $criteria->limit;
		$remainingElements = $criteria->total() - $totalVisible;

		$this->returnJson(array(
			'elementContainerHtml' => $elementContainerHtml,
			'elementDataHtml'      => $elementDataHtml,
			'headHtml'             => craft()->templates->getHeadHtml(),
			'totalVisible'         => $totalVisible,
			'more'                 => ($remainingElements > 0),
		));
	}

	/**
	 * Returns the element type based on the posted element type class.
	 *
	 * @access private
	 * @return BaseElementType
	 * @throws Exception
	 */
	private function _getElementType()
	{
		$class = craft()->request->getRequiredParam('elementType');
		$elementType = craft()->elements->getElementType($class);

		if (!$elementType)
		{
			throw new Exception(Craft::t('No element type exists with the class "{class}"', array('class' => $class)));
		}

		return $elementType;
	}
}
