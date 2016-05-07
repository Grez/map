<?php

namespace Teddy\Map\Components;

use Teddy;
use Nette\Application\UI\Control;
use Teddy\Map\Map;
use Teddy\Map\Position;


class MapControl extends Control
{

	/**
	 * @var Map
	 */
	protected $map;

	/**
	 * @var Position
	 */
	protected $startPosition;

	/**
	 * @var bool
	 */
	protected $renderMap = FALSE;



	public function __construct(Map $map, Position $startPosition)
	{
		parent::__construct();
		$this->map = $map;
		$this->startPosition = $startPosition;
	}



	/**
	 * @param $renderMap
	 */
	public function setRenderMap($renderMap)
	{
		$this->renderMap = $renderMap;
	}



	/**
	 * Forces map rendering
	 */
	public function handleRenderMap()
	{
		$this->renderMap = TRUE;
		$this->redrawControl();
	}



	public function render()
	{
		$template = parent::createTemplate();
		$template->map = $this->map;
		$template->startPosition = $this->startPosition;
		$template->renderMap = $this->renderMap;
		$template->setFile(__DIR__ . '/map.latte');
		$template->render();
	}

}



interface IMapControlFactory
{

	/**
	 * @param Map $map
	 * @param Position $startPosition
	 * @return MapControl
	 */
	public function create(Map $map, Position $startPosition);
}
