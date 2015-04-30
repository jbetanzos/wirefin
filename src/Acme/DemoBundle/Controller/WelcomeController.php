<?php

namespace Acme\DemoBundle\Controller;

use Acme\DemoBundle\Entity\Contact;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class WelcomeController extends Controller
{

    /**
     * @Route("/", name="welcome")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $siteInformation = $em->getRepository('AcmeDemoBundle:Site')->find(1);
        $categories = $em->getRepository('AcmeDemoBundle:Category')->findAll();
        $products = array();

        foreach($categories as $category) {
            $products[$category->getSlug()] =
                $em->getRepository('AcmeDemoBundle:Product')
                    ->findBy(
                        array('category' => $category),
                        null,
                        5
                    );
        }

        $form = $this->createFormBuilder(new Contact())
                ->add('name', 'text')
                ->add('email', 'email')
                ->add('phone', 'text')
                ->add('category', 'entity', array(
                    'class' => 'AcmeDemoBundle:Category',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->orderBy('c.name', 'ASC');
                    }
                ))
                ->add('commets', 'text')
                ->add('recaptcha', 'ewz_recaptcha')
                ->add('save', 'submit', array('label' => 'Enviar'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $contact = $form->getData();
            $em->persist($contact);
            $em->flush();
        }

        $view = 'AcmeDemoBundle:Welcome:index.html.twig';
        $response = $this->render(
            $view,
            array(
                'SiteInformation' => $siteInformation,
                'Categories' => $categories,
                'Products' => $products,
                'ContactForm' => $form->createView()
            )
        );

        return $response;
    }

    /**
     * @Route("/details/{id}", name="welcome_details")
     */
    public function detailsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AcmeDemoBundle:Product')
            ->find($id);

        $view = 'AcmeDemoBundle:Welcome:details.html.twig';

        return $this->render(
                $view,
                array(
                    'Product' => $product
                )
            );
    }
}
