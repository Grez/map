<?php

namespace Game\Map;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;



/**
 * @ORM\Entity
 */
class Map extends \Teddy\Map\Map
{

	use Identifier;

}
