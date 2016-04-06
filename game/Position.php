<?php

namespace Game\Map;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;



/**
 * @ORM\Entity(readOnly=TRUE)
 */
class Position extends \Teddy\Map\Position
{

	use Identifier;

}
