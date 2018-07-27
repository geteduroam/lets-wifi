<?php declare(strict_types=1);

/**
 * Distinguished Name implementation
 *
 * Copyright: 2018, Uninett AS
 * SPDX-License-Identifier: BSD-3-Clause
 */

namespace Uninett\LetsWifi\X509;

class DN implements IDN
{
	/** @var array */
	private $subjectFields;

	/**
	 * List of subject fields
	 *
	 * @param array $subjectFields
	 */
	public function __construct( array $subjectFields )
	{
		$this->subjectFields = $subjectFields;
	}

	/**
	 * Shorthand for getSubjectString()
	 *
	 * @see getSubjectString()
	 *
	 * @return string The subject string
	 */
	public function __toString(): string
	{
		return $this->getSubjectString();
	}

	/** {@inheritdoc} */
	public function getSubjectString(): string
	{
		$result = [];
		$search = ['\\', ',', '='];
		$replace = ['\\\\', '\\,', '\\='];
		foreach ( $this->subjectFields as $fieldName => $field ) {
			$result[] = \str_replace( $search, $replace, $fieldName ) . '=' . \str_replace( $search, $replace, $field );
		}

		return \implode( ', ', $result );
	}

	/** {@inheritdoc} */
	public function getSubjectFields(): array
	{
		return $this->subjectFields;
	}
}
