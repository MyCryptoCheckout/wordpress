<?php

namespace BitWasp\Buffertools;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Math\GmpMathInterface;

class TemplateFactory
{
    /**
     * @var GmpMathInterface
     */
    private $math;

    /**
     * @var \BitWasp\Buffertools\Template
     */
    private $template;

    /**
     * @var TypeFactory
     */
    private $types;

    /**
     * TemplateFactory constructor.
     * @param Template|null $template
     * @param GmpMathInterface|null $math
     * @param TypeFactoryInterface|null $typeFactory
     */
    public function __construct(Template $template = null, GmpMathInterface $math = null, TypeFactoryInterface $typeFactory = null)
    {
        $this->math = $math ?: EccFactory::getAdapter();
        $this->template = $template ?: new Template();
        $this->types = $typeFactory ?: new CachingTypeFactory();
    }

    /**
     * Return the Template as it stands.
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Add a Uint8 serializer to the template
     *
     * @return $this
     */
    public function uint8()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Uint8 serializer to the template
     *
     * @return $this
     */
    public function uint8le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a Uint16 serializer to the template
     *
     * @return $this
     */
    public function uint16()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Uint16 serializer to the template
     *
     * @return $this
     */
    public function uint16le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a Uint32 serializer to the template
     *
     * @return $this
     */
    public function uint32()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Uint32 serializer to the template
     *
     * @return $this
     */
    public function uint32le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a Uint64 serializer to the template
     *
     * @return $this
     */
    public function uint64()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Uint64 serializer to the template
     *
     * @return $this
     */
    public function uint64le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a Uint128 serializer to the template
     *
     * @return $this
     */
    public function uint128()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Uint128 serializer to the template
     *
     * @return $this
     */
    public function uint128le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a Uint256 serializer to the template
     *
     * @return $this
     */
    public function uint256()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Uint256 serializer to the template
     *
     * @return $this
     */
    public function uint256le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a int8 serializer to the template
     *
     * @return $this
     */
    public function int8()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Int8 serializer to the template
     *
     * @return $this
     */
    public function int8le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a int16 serializer to the template
     *
     * @return $this
     */
    public function int16()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Int16 serializer to the template
     *
     * @return $this
     */
    public function int16le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a int32 serializer to the template
     *
     * @return $this
     */
    public function int32()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Int serializer to the template
     *
     * @return $this
     */
    public function int32le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a int64 serializer to the template
     *
     * @return $this
     */
    public function int64()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Int64 serializer to the template
     *
     * @return $this
     */
    public function int64le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a int128 serializer to the template
     *
     * @return $this
     */
    public function int128()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Int128 serializer to the template
     *
     * @return $this
     */
    public function int128le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a int256 serializer to the template
     *
     * @return $this
     */
    public function int256()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a little-endian Int256 serializer to the template
     *
     * @return $this
     */
    public function int256le()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a VarInt serializer to the template
     *
     * @return $this
     */
    public function varint()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a VarString serializer to the template
     *
     * @return $this
     */
    public function varstring()
    {
        $this->template->addItem($this->types->{__FUNCTION__}());
        return $this;
    }

    /**
     * Add a byte string serializer to the template. This serializer requires a length to
     * pad/truncate to.
     *
     * @param  $length
     * @return $this
     */
    public function bytestring($length)
    {
        $this->template->addItem($this->types->{__FUNCTION__}($length));
        return $this;
    }

    /**
     * Add a little-endian byte string serializer to the template. This serializer requires
     * a length to pad/truncate to.
     *
     * @param  $length
     * @return $this
     */
    public function bytestringle($length)
    {
        $this->template->addItem($this->types->{__FUNCTION__}($length));
        return $this;
    }

    /**
     * Add a vector serializer to the template. A $readHandler must be provided if the
     * template will be used to deserialize a vector, since it's contents are not known.
     *
     * The $readHandler should operate on the parser reference, reading the bytes for each
     * item in the collection.
     *
     * @param  callable $readHandler
     * @return $this
     */
    public function vector(callable $readHandler)
    {
        $this->template->addItem($this->types->{__FUNCTION__}($readHandler));
        return $this;
    }
}
