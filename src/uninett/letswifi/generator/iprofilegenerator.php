<?php declare(strict_types=1);

/**
 * Profile generator
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

interface IProfileGenerator
{
	/**
	 * Generate a profile
	 */
	public function __toString(): string;

	/**
	 * Content type for the profile
	 */
	public function getContentType(): string;
}
