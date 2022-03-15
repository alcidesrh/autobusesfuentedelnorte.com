<?php
namespace Acme\TerminalOmnibusBundle\Form\Frontend\Salida;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CancelarSalidaType extends AbstractType{
    
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
       $salida = $builder->getData();
       $builder->add('id', 'hidden');
       $totalBoletosChequeados = 0;
       $totalBoletosNOChequeados = 0;
       $totalEncomiendasEmbarcadas = 0;
       $totalReservacionesEmitidas = 0;
       $idSalida = $salida->getId();
       if( $idSalida !== null && trim($idSalida) !== "" && trim($idSalida) !== "0"){
            $totalBoletosChequeados = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosChequeadosBySalida($idSalida);
            $totalBoletosNOChequeados = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosEmitidosBySalida($idSalida);
            $totalBoletosNOChequeados += $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Boleto')->totalBoletosEmitidosPendientesBySalida($idSalida);
            $totalEncomiendasEmbarcadas = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Encomienda')->totalEncomiendasEmbarcadasBySalida($idSalida);            
            $totalReservacionesEmitidas = $this->doctrine->getManager()->getRepository('AcmeTerminalOmnibusBundle:Reservacion')->totalReservacionesEmitidasBySalida($idSalida);
       }
       
       $builder->add('totalBoletosChequeados', 'text',  array(
            'label' => 'Total de boletos chequeados', 
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalBoletosChequeados,
            'attr' =>  array('class' => 'span9'),
        ));
       
        $builder->add('totalBoletosNOChequeados', 'text',  array(
            'label' => 'Total de boletos emitidos que aún no se han chequeado', 
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalBoletosNOChequeados,
            'attr' =>  array('class' => 'span9'),
        ));
        
        $builder->add('totalEncomiendasEmbarcadas', 'text',  array(
            'label' => 'Total de encomiendas embarcadas',
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalEncomiendasEmbarcadas,
            'attr' =>  array('class' => 'span9'),
        ));
        
        $builder->add('totalReservacionesEmitidas', 'text',  array(
            'label' => 'Total de reservaciones pendientes',
            'read_only' => true,
            'virtual' => true,
            'required' => false,
            'data' => $totalReservacionesEmitidas,
            'attr' =>  array('class' => 'span9'),
        ));
        
    }

    public function getName()
    {
        return 'cancelar_salida_command';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) 
    {
       	$resolver->setDefaults(array(
            'data_class' => 'Acme\TerminalOmnibusBundle\Entity\Salida',
 	    'cascade_validation' => true,
    	));
    }
}

?>
