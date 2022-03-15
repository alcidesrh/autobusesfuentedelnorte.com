<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Encomienda;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoEncomienda;
use Acme\TerminalOmnibusBundle\Entity\TipoEncomienda;

class AnularEncomiendaType extends AbstractType{
    
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
//       $encomienda = $builder->getData();
       $builder->add('id', 'hidden');
       
       //Factura
//       $valor = 0;
//       if($encomienda->getTipoDocumento() !== null && $encomienda->getTipoDocumento()->getId() === TipoDocumentoEncomienda::FACTURA){
//           $valor = $encomienda->getPrecioCalculadoMonedaBase();     
//       }
//       if($encomienda->getTipoEncomienda()->getId() === TipoEncomienda::EFECTIVO){
//           $valor += $encomienda->getCantidad();
//       }
//       
//       $builder->add('importeEntregar', 'text',  array(
//            'label' => 'Importe a entregar',
//            'required' => true,
//            'virtual' => true,
//            'read_only' => true,
//            'data' => $valor,
//            'pattern' => ".*((^\d{0,8}$)|(^\d{1,8}[\.|,]\d{1,2}$)).*"
//       ));
       
       $builder->add('observacion', null, array(
             'label' => 'Motivo',
             'required' => true,
             'attr' =>  array('class' => 'span12')
        ));
    }

    public function getName()
    {
        return 'anular_encomienda_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Encomienda',
 	    'cascade_validation' => true,
    	));
    }
}

?>
