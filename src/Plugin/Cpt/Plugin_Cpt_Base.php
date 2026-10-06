<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Cpt;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Labels\Cpt_Labels;

defined( 'ABSPATH' ) || exit;

class Plugin_Cpt_Base implements Plugin_Cpt {
	protected string $cpt_name = '';
	protected Cpt_Labels $labels;
	protected string $slug_prefix = '';
	protected string $folder_name = '';

	public function cpt_name(): string {
		return $this->cpt_name;
	}

	public function labels(): Cpt_Labels {
		return $this->labels;
	}

	public function slug_prefix(): string {
		return $this->slug_prefix;
	}

	public function folder_name(): string {
		return $this->folder_name;
	}
}
