<?php

/**
 * Class for the <produits> elements
 * 
 * @author CYRILLE Yann <yann.cyrille at fia-net.com>
 */
class CertissimOrderProducts extends CertissimXMLElement
{

	public function __construct()
	{
		parent::__construct('produits');
	}

	/**
	 * creates a CertissimProduct object representing element <produits>, adds it to the current element, adds sub-children, then returns it
	 * 
	 * @param string $codeean ean code product
	 * @param string $id id product
	 * @param int $categorie FIA-NET category id
	 * @param string $libelle product name
	 * @param float $montant product amount
	 * @param string $image product url image
	 * @return CertissimProduct
	 */
	public function createProduct($codeean, $id, $categorie, $libelle, $montant, $image)
	{
		$product = $this->addChild(new CertissimProduct());
		if (!is_null($codeean))
			$product->createChild('codeean', $codeean);
		$product->createChild('id', $id);
		$product->createChild('categorie', $categorie);
		$product->createChild('libelle', $libelle);
		$product->createChild('montant', $montant);
		if (!is_null($image))
			$product->createChild('image', $image);

		return $product;
	}

}

