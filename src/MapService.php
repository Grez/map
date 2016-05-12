<?php

namespace Teddy\Map;

use Kdyby\Clock\IDateTimeProvider;
use Kdyby\Doctrine\EntityManager;
use NoiseGenerator\PerlinNoise;



/**
 * @method void onEmbiggen(MapService $mapService, \Game\Map\Map $map)
 */
class MapService extends \Nette\Object
{

	/**
	 * @var array
	 */
	public $onEmbiggen = [];

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var IDateTimeProvider
	 */
	protected $timeProvider;



	public function __construct(EntityManager $em, IDateTimeProvider $timeProvider)
	{
		$this->em = $em;
		$this->timeProvider = $timeProvider;
	}



	/**
	 * @param $id
	 * @return null|\Game\Map\Map
	 */
	public function getMap($id)
	{
		return $this->em->find(\Game\Map\Map::class, $id);
	}



	/**
	 * @return \Game\Map\Map
	 */
	public function createMap($radius, $seed = NULL, $octaves = NULL, $elevation = NULL)
	{
		$map = new \Game\Map\Map($seed, $octaves, $elevation);
		$this->em->persist($map);
		$this->em->flush($map);
		$this->embiggenMapBy($map, $radius);
		return $map;
	}



	/**
	 * Makes map bigger by $embiggenBy
	 * http://cs.urbandictionary.com/define.php?term=Embiggen
	 *
	 * @param \Game\Map\Map $map
	 * @param int $embiggenBy
	 * @return \Game\Map\Map
	 */
	public function embiggenMapBy(\Game\Map\Map $map, $embiggenBy)
	{
		for ($i = 0; $i < $embiggenBy; $i++) {
			$positions = $this->addBorderToMap($map);
			foreach ($positions as $position) {
				$this->em->persist($position);
			}

			// We want to do this in single trasaction
			$map->setPositionsLastModifiedAt($this->timeProvider->getDateTime());
			$this->em->flush(array_merge([$map], $positions));
			$this->em->clear(\Game\Map\Position::class);
			$this->onEmbiggen($this, $map);
		}

		return $map;
	}



	/**
	 * We add border
	 * Generated in this order ("=" are starting positions, number represents step)
	 *
	 * 1112
	 * 4==2
	 * 4==2
	 * 4333
	 *
	 * @param \Game\Map\Map $map
	 * @return \Game\Map\Position[]
	 */
	protected function addBorderToMap(\Game\Map\Map $map)
	{
		// There is nothing in map
		if ($map->getRadius() === 0) {
			$map->increaseMaxDistance();
			return [$this->createPosition($map, 0, 0)];
		}

		$newPositions = [];
		for ($x = $map->getRadius() * -1; $x <= $map->getRadius() - 1; $x++) {
			$newPositions[] = $this->createPosition($map, $x, $map->getRadius());
		}

		for ($y = $map->getRadius(); $y >= $map->getRadius() * -1 + 1; $y--) {
			$newPositions[] = $this->createPosition($map, $map->getRadius(), $y);
		}

		for ($x = $map->getRadius(); $x >= $map->getRadius() * -1 + 1; $x--) {
			$newPositions[] = $this->createPosition($map, $x, $map->getRadius() * -1);
		}

		for ($y = $map->getRadius() * -1; $y <= $map->getRadius() - 1; $y++) {
			$newPositions[] = $this->createPosition($map, $map->getRadius() * -1, $y);
		}

		$map->increaseMaxDistance();
		return $newPositions;
	}



	/**
	 * @param \Game\Map\Map $map
	 * @param int $x
	 * @param int $y
	 * @param bool $addToMap - when the position isn't added to map we don't have to fetch already generated positions
	 * @return \Game\Map\Position
	 */
	protected function createPosition(\Game\Map\Map $map, $x, $y, $addToMap = FALSE)
	{
		$perlin = new PerlinNoise($map->getSeed());
		$num = $perlin->noise($map->getElevation() * $x, $map->getElevation() * $y, 0, $map->getOctaves());
		$height = ($num / 2) + 0.5;

		$position = new \Game\Map\Position($map, $x, $y, $height);
		if ($addToMap) {
			$map->addPosition($position);
		}
		return $position;
	}



	/**
	 * Return javascript Graph (astar.js)
	 *
	 * @param int $mapId
	 * @return string
	 */
	public function getJsIncidenceMatrix($mapId)
	{
		$map = $this->em->find(\Game\Map\Map::class, $mapId);
		if (!$map) {
			throw new \InvalidArgumentException('Map doesn\'t exist');
		}

		$js = 'new window.Graph(';
		$js .= '[' . "\n";

		for ($x = $map->getRadius() * -1 + 1; $x < $map->getRadius() - 1; $x++) {
			$js .= '[';
			$weights = [];
			for ($y = $map->getRadius() * -1 + 1; $y < $map->getRadius() - 1; $y++) {
				$position = $map->getPosition($x, $y);
				$weights[] = $position->getWeight() + 1;
			}
			$js .= implode(',', $weights);
			$js .= '],' . "\n";
		}

		$js .= ']';
		$js .= ');';
		return $js;
	}

}
