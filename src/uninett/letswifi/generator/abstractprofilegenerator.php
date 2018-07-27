<?php declare(strict_types=1);

/**
 * Abstract profile generator.
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

use Uninett\LetsWifi\Authentication\IAuthenticationMethod;

abstract class AbstractProfileGenerator implements IProfileGenerator
{
	/**
	 * List of authentication methods.
	 *
	 * @var IAuthenticationMethod[]
	 */
	protected $authentication;

	/**
	 * Metadata for this profile
	 *
	 * @var IProfileMetadata
	 */
	protected $metadata;

	/**
	 * Create a new generator.
	 *
	 * @param IAuthenticationMethod[] $authentication Authentication methods
	 */
	public function __construct( IProfileMetadata $metadata, array $authentication )
	{
		$this->metadata = $metadata;
		$this->authentication = $authentication;
	}
}
