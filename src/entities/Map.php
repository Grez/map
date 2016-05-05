<?php

namespace Teddy\Map;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\MappedSuperclass()
 */
class Map
{

	/**
	 * @ORM\OneToMany(targetEntity="Position", mappedBy="map", indexBy="id", fetch="LAZY")
	 * @var Position[]
	 */
	protected $positions;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $positionsLastModifiedAt;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	protected $radius;

	/**
	 * Used for Perlin noise
	 *
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	protected $seed;

	/**
	 * Used for Perlin noise
	 *
	 * @ORM\Column(type="array")
	 * @var int[]
	 */
	protected $octaves;

	/**
	 * Used for Perlin noise
	 *
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $elevation;



	public function __construct($seed = NULL, $octaves = NULL, $elevation = NULL)
	{
		$this->positions = new ArrayCollection();
		$this->radius = 0;
		$this->seed = $seed ?: mt_rand(1, 2e8);
		$this->octaves = $octaves ?: [64, 32, 16, 4];
		$this->elevation = $elevation ?: 4.2;
		$this->positionsLastModifiedAt = new \DateTime();
	}



	/**
	 * @param Position $position
	 */
	public function addPosition(Position $position)
	{
		$this->positions[$position->getId()] = $position;
	}



	/**
	 * @param int $x
	 * @param int $y
	 * @return Position|NULL
	 */
	public function getPosition($x, $y)
	{
		if (!isset($this->positions[$this->getId() . ';' . $x . ';' . $y])) {
			throw new \InvalidArgumentException('Position not found. Was it added to entity?');
		}

		return $this->positions->get($this->getId() . ';' . $x . ';' . $y);
	}



	/**
	 * @return Position[]
	 */
	public function getPositions()
	{
		return $this->positions;
	}



	/**
	 * @return int
	 */
	public function getRadius()
	{
		return $this->radius;
	}



	/**
	 * @return Map
	 */
	public function increaseMaxDistance()
	{
		$this->setPositionsLastModifiedAt(new \DateTime());
		$this->radius++;
		return $this;
	}



	/**
	 * @return int
	 */
	public function getSeed()
	{
		return $this->seed;
	}



	/**
	 * @return int[]
	 */
	public function getOctaves()
	{
		return $this->octaves;
	}



	/**
	 * @return \DateTime
	 */
	public function getPositionsLastModifiedAt()
	{
		return $this->positionsLastModifiedAt;
	}



	/**
	 * @param \DateTime $positionsLastModifiedAt
	 * @return Map
	 */
	public function setPositionsLastModifiedAt(\DateTime $positionsLastModifiedAt)
	{
		$this->positionsLastModifiedAt = $positionsLastModifiedAt;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getJsIncidenceMatrix()
	{
		$js = '[' . "\n";

		for ($x = $this->getRadius() * -1 + 1; $x < $this->getRadius() - 1; $x++) {
			$js .= '[';
			$weights = [];
			for ($y = $this->getRadius() * -1 + 1; $y < $this->getRadius() - 1; $y++) {
				$position = $this->getPosition($x, $y);
				$weights[] = $position->getWeight() + 1;
			}
			$js .= implode(',', $weights);
			$js .= '],' . "\n";
		}

		$js .= ']';
		return $js;
	}



	/**
	 * @return float
	 */
	public function getElevation()
	{
		return $this->elevation;
	}



	/**
	 * @param float $elevation
	 * @return Map
	 */
	public function setElevation($elevation)
	{
		$this->elevation = $elevation;
		return $this;
	}



	/**
	 * @param int[] $octaves
	 * @return Map
	 */
	public function setOctaves($octaves)
	{
		$this->octaves = $octaves;
		return $this;
	}



	/**
	 * @param int $seed
	 * @return Map
	 */
	public function setSeed($seed)
	{
		$this->seed = $seed;
		return $this;
	}

}
