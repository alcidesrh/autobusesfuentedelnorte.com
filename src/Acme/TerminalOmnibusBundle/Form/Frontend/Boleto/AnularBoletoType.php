<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Boleto;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;
use Acme\BackendBundle\Services\UtilService;

class AnularBoletoType extends AbstractType{
    
    protected $doctrine = null;
    
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        /*****************************************
         *          OTRAS PROPIEDADES
         * ***************************************
         *  'required' => true,
         *  'read_only' => true,
         *  
         */ 
//       $entityManager = $options['em'];
       $boleto = $builder->getData();
       $builder->add('id', 'hidden');
               
       $valor = 0;
       //Factura
       if($boleto->getTipoDocumento() !== null){
           if($boleto->getTipoDocumento()->getId() === TipoDocumentoBoleto::VOUCHER_AGENCIA){
               $valor = UtilService::calcularPrecioTotalReasignado($boleto); //Valor en la moneda base de la agencia
           }else{
               $valor = UtilService::calcularPrecioTotalReasignadoMonedaBase($boleto);
           }
       }
      
       $builder->add('importeEntregar', 'text',  array(
            'label' => 'Importe a entregar',
            'required' => true,
            'virtual' => true,
            'read_only' => true,
            'data' => $valor,
            'pattern' => ".*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*"
       ));
       
       $builder->add('observacion', null, array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'anular_boleto_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Boleto',
 	    'cascade_validation' => true,
    	));
    }
}

?>
