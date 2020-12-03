<?php


namespace App\Controller;


use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\Partner;
use App\Entity\Post;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class BezoekerController extends AbstractController
{
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="homepage")
     */
    function indexAction(Request $request, MailerInterface $mailer)
    {
        $nieuwsberichten = $this->em->getRepository(Post::class)->findLatest();
        $partners=$this->em->getRepository(Partner::class)->findAll();

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact, [
            'action' => $this->generateUrl('submitContactForm'),
            'method' => 'GET',
        ]);
        
        $response =  $this->render('bezoeker/home.html.twig', [
            'form' => $form->createView(),
            'nieuwsberichten' => $nieuwsberichten,
            'partners'=>$partners
        ]);

        return $response;

    }

    /**
     * @Route("/submitContactForm", name="submitContactForm")
     */
    function submitContactForm(Request $request, MailerInterface $mailer)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact, [
            'action' => $this->generateUrl('submitContactForm'),
            'method' => 'GET',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // create new contact
            $contact = $form->getData();
            $name = $form->get('name')->getData();
            if (empty($name)) {
                throw new BadRequestHttpException('name cannot be empty');
            }
            $emailAddress = $form->get('email')->getData();
            if (empty($emailAddress)) {
                throw new BadRequestHttpException('email cannot be empty');
            }
            $message = $form->get('message')->getData();
            if (empty($message)) {
                throw new BadRequestHttpException('message cannot be empty');
            }
// contact wishes to receive newsletter
            $subscribed = $form->get('subscribed')->getData();
            $contact->setName($name);
            $contact->setEmail($emailAddress);
            $contact->setMessage($message);
            $contact->setSubscribed($subscribed);
// make email
            $html = "<p>Er is een bericht van $emailAddress.</p>
                     <p>$message</p>";
            
            if ($subscribed == true) {
                $html .= "<p>Deze persoon heeft zich ook aangemeld voor de nieuwsbrief</p>";
            }

            $email = (new Email())
                ->from($emailAddress)
                ->to('ewahaaglanden@rocmondriaan.nl')
                ->subject('E-mail van EWA Haaglanden met bericht')
                ->html($html);

            $mailer->send($email);
            $email = (new TemplatedEmail())
                ->from('no-reply@ewahaaglanden.nl')
                ->to($emailAddress)
                ->subject('Uw bericht is ontvangen')
                ->htmlTemplate('emails/registration.html.twig')
                ->context([
                    'name' => $name,
                ]);

            $mailer->send($email);
            $this->addFlash('success',"Hartelijk dank, uw bericht is verzonden.");
            $this->getDoctrine()->getManager()->persist($contact);
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirectToRoute('homepage');
    }
    /**
     * @Route("/Info/{name}", name="showLink")
     */
    function showLink(Request $request, $name)
    {
        $informatie = $this->em->getRepository(Document::class)->findOneBy(['name' => $name]);
        return $this->render('bezoeker/showInformatieDetails.html.twig', [
            'informatie' => $informatie,
        ]);
    }

    /**
     * @Route("/nieuws/{id}", name="showNieuwsdetail")
     */
    function showNieuwsDetailsAction(Request $request, $id)
    {
        $nieuwsbericht = $this->em->getRepository(Post::class)->find($id);
        return $this->render('bezoeker/showNieuwsbericht.html.twig', [
            'nieuwsbericht' => $nieuwsbericht,
        ]);
    }

    /**
     * @Route("/test", name="showTest")
     */
    function showTestAction(Request $request, MailerInterface $mailer)
    {   
        $nieuwsberichten = $this->em->getRepository(Post::class)->findLatest();
        $partners=$this->em->getRepository(Partner::class)->findAll();
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

// create new contact
            $contact = $form->getData();
            $name = $form->get('name')->getData();
            if (empty($name)) {
                throw new BadRequestHttpException('name cannot be empty');
            }
            $emailAddress = $form->get('email')->getData();
            if (empty($emailAddress)) {
                throw new BadRequestHttpException('email cannot be empty');
            }
            $message = $form->get('message')->getData();
            if (empty($message)) {
                throw new BadRequestHttpException('message cannot be empty');
            }
// contact wishes to receive newsletter
            $subscribed = $form->get('subscribed')->getData();
            $contact->setName($name);
            $contact->setEmail($emailAddress);
            $contact->setMessage($message);
            $contact->setSubscribed($subscribed);
// make email
            $html = "<p>Beste mevrouw A. Waarts, </p>
                     <p>U heeft een bericht ontvangen via de website van EWA Haaglanden van $emailAddress.  </p>
                     <p>$message</p>";
            
            if ($subscribed == true) {
                $html .= "<p>Deze persoon heeft zich ook aangemeld voor de nieuwsbrief</p>";
            }

            $email = (new Email())
                ->from($emailAddress)
                ->to('ewahaaglanden@rocmondriaan.nl')
                ->subject('E-mail van EWA Haaglanden met bericht')
                ->html($html);

            $mailer->send($email);
            $email = (new TemplatedEmail())
                ->from('no-reply@ewahaaglanden.nl')
                ->to($emailAddress)
                ->subject('Uw bericht is ontvangen')
                ->htmlTemplate('emails/registration.html.twig')
                ->context([
                    'name' => $name,
                ]);

            $mailer->send($email);
            $this->addFlash('success',"Hartelijk dank, uw bericht is verzonden.");
            $this->getDoctrine()->getManager()->persist($contact);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('homeCreate');
        }
        return $this->render('test.html.twig', [
            'form' => $form->createView(),
            'nieuwsberichten' => $nieuwsberichten,
            'partners'=>$partners,
        ]);
    }

    /**
     * @Route("/nieuws", name="showNieuws")
     */
    public function findAllNbAction()
    {
        $news = $this->em->getRepository(Post::class)->findBy([], ['id' => 'DESC']);
        foreach($news as $nw)
        {
            $nw->setContent(substr($nw->getContent(),0,200));
            $nw->setContent($nw->getContent() . "...");
        }

        return $this->render('bezoeker/showNieuwsberichten.html.twig', [
            'news' => $news,
        ]);
    }

    /**
     * @Route("/partners", name="showPartners")
     */
    public function findAllPAction()
    {
        $partners = $this->em->getRepository(Partner::class)->findBy([], ['id' => 'DESC']);

        return $this->render('bezoeker/showPartners.html.twig', [
            'partners' => $partners,
        ]);
    }
    /**
     * @Route("/informatie", name="showInformatie")
     */
    public function findAllIAction()
    {
        $information = $this->em->getRepository(Document::class)->findBy([], ['id' => 'DESC']);

        return $this->render('bezoeker/showInformatie.html.twig', [
            'informatietotaal' => $information,
        ]);
    }

    /**
     * @Route("/informatie/{id}", name="showInformatieDetails")
     */
    public function showInformationDetails(Request $request, $id)
    {
        $informatie = $this->em->getRepository(Document::class)->findOneBy(['id' => $id]);

        return $this->render('bezoeker/showInformatieDetails.html.twig', [
            'informatie' => $informatie,
        ]);
    }


    public function makeNavBar($current){
        $currentRoute = $current;
        $routes = [
            'Home' => 'homepage',
            'Nieuws' => 'showNieuws',
            'Partners' => 'showPartners',
            'Informatie' => 'showInformatie',
        ];

        return $this->render('navBar.html.twig', [
            'routes' => $routes,
            'currentRoute' => $currentRoute,
        ]);
    }
}