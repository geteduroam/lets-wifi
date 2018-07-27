<?php declare(strict_types=1);

/**
 * Logo
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\Generator;

interface ILogo
{
	public function getContentType(): string;

	public function getBytes(): string;
}
