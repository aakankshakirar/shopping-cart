<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Util\Common;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\OrderType;
use App\Entity\Category;
use App\Entity\UserMeta;
use App\Entity\OrderItem;
use App\Form\UserMetaType;
use App\Form\UserOrderType;
use Doctrine\DBAL\Connection;
use App\Service\MessageGenerator;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserMetaRepository;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpFoundation\Session\Session;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\CacheInterface;

class StoreController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $request->setLocale('es');
        $message = new TranslatableMessage("symfony.is.great");

        return $this->render('store/index.html.twig', [
            'products' => $productRepository->findAll()
        ]);
    }

    /**
     * @Route("product/add_cart/{id}", name="add_cart_item")
     */
    public function addToCart(Product $product, Request $request, TranslatorInterface $translator)
    {
        // dd('hi aakanksha');
        // Check for quantity
        $quantity = $request->request->get('quantity');
        if ($quantity <= 0) {
            $this->addFlash('error', $translator->trans('quantity can not be less'));

            return $this->render('store/show_product.html.twig', [
                'product' => $product
            ]);
        }

        // Using the symfony Session service
        $cart = $this->get('session')->get('cart');

        // if cart is empty then this the first product
        if (!$cart) {
            $cart = [
                $product->getId() => [
                    "id" => $product->getId(),
                    "title" => $product->getTitle(),
                    "quantity" => $request->request->get('quantity'),
                    "price" => $product->getPrice(),
                    "image" => $product->getImage(),
                    "subtotal" => ($request->request->get('quantity')) * ($product->getPrice())
                ]
            ];
            $this->get('session')->set('cart', $cart);
        }
        // if item not exist in cart then add to cart with quantity 
        $cart[$product->getId()] = [
            "id" => $product->getId(),
            "title" => $product->getTitle(),
            "quantity" => $request->request->get('quantity'),
            "price" => $product->getPrice(),
            "image" => $product->getImage(),
            "subtotal" => ($request->request->get('quantity')) * ($product->getPrice())
        ];
        $this->get('session')->set('cart', $cart);

        $this->addFlash('success', 'Product added to cart successfully ');

        return $this->render('store/show_product.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_show", methods={"GET"})
     */
    public function showProduct(Product $product): Response
    {
        return $this->render('store/show_product.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("/view_cart", name="view_cart")
     *
     */
    public function viewCart()
    {
        $cart = $this->get('session')->get('cart');

        return $this->render('store/cart.html.twig', [
            'cart' => $cart
        ]);
    }

    /**
     * @Route("/checkout", name="checkout" , methods={"GET","POST"})
     */
    public function checkout(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $cart = $this->get('session')->get('cart');
        $user = $this->getUser();
        $order = new Order();

        $userMeta = $this->getDoctrine()->getRepository(UserMeta::class)->findOneBy(array('user' => $user));

        // Update user's additional information
        if (empty($userMeta)) {
            $userMeta = new UserMeta();
        }
        $formData = [
            'usermeta' => $userMeta,
            'order' => $order,
            'cart' => $cart,
            'em' => $entityManager
        ];

        $mix_form = $this->createForm(UserOrderType::class, $formData);
        $mix_form->handleRequest($request);

        if ($mix_form->isSubmitted() && $mix_form->isValid()) {

            $entityManager->persist($userMeta);
            $entityManager->persist($order);

            $entityManager->flush();

            return $this->redirectToRoute('success_order');
        }

        return $this->render('store/checkout.html.twig', [
            'cart' => $cart,
            'errors' => null,
            'form_user_meta' => $mix_form->createView()
        ]);
    }

    /**
     * @Route("/place_order", name="place_order")
     */
    public function placeOrder(Request $request)
    {
        $user = $this->getUser();
        $cart = $this->get('session')->get('cart');

        // Calling the Utility class
        // $util = new Common();
        // $myString = $util->callMe("my name is aakanksha");
        // dd($myString);

        $entityManager = $this->getDoctrine()->getManager();


        if (!empty($user) && !empty($cart)) {

            // Check for validations
            $result = self::customFormValidation($request);

            if (!$result['status']) {
                return $this->render('store/checkout.html.twig', [
                    'cart' => $cart,
                    'errors' => $result['errors']
                ]);
            }

            try {
                $userMeta = $this->getDoctrine()->getRepository(UserMeta::class)->findOneBy(array('user' => $user));

                // Update user's additional information
                if (empty($userMeta)) {
                    $userMeta = new UserMeta();
                }

                $userMeta->setUser($user);

                // This Code requires refactoring
                $userMeta->setFirstname($request->request->get('firstname'));
                $userMeta->setLastname($request->request->get('lastname'));
                $userMeta->setContact($request->request->get('contact'));
                $userMeta->setAddress($request->request->get('address'));
                $userMeta->setCity($request->request->get('city'));
                $userMeta->setPincode($request->request->get('pincode'));

                // Get the items added in the session
                $cart = $this->get('session')->get('cart');

                $total_amount = 0;

                foreach ($cart as $key => $item) {
                    $total_amount += $item['subtotal'];
                }

                $entityManager->persist($userMeta);

                // Create Order
                $order = new Order();
                $order->setUser($user);
                $order->setTotalAmount($total_amount);
                $order->setNote($request->request->get('note'));
                $order->setDateCreated(new DateTime());
                $order->setDateUpdated(new DateTime());

                $entityManager->persist($order);

                foreach ($cart as $key => $item) {

                    // Create order item
                    $orderItem = new OrderItem();

                    $product = $this->getDoctrine()->getRepository(Product::class)->find($item['id']);

                    $orderItem->setQuantity($item['quantity']);
                    $orderItem->setAmount($item['subtotal']);
                    $orderItem->setParentOrder($order);
                    $orderItem->setProduct($product);

                    $entityManager->persist($orderItem);
                }

                $entityManager->flush();

                return $this->redirectToRoute('success_order');
            } catch (Exception $e) {
                throw $e;
            }
        }

        return $this->redirectToRoute('checkout');
    }

    /**
     * @Route("/success", name="success_order")
     */
    public function successOrder()
    {
        //clear Session
        $this->get('session')->remove('cart');

        return $this->render('store/success.html.twig');
    }

    /**
     * @Route("/delete/{id}", name="delete_cart_item")
     */
    public function deleteCartItem(Request $request)
    {
        $id = $request->attributes->get('id');

        if ($id) {
            $cart = $this->get('session')->get('cart');

            if (isset($cart[$id])) {
                unset($cart[$id]);

                if (empty($cart)) {
                    $this->get('session')->remove('cart');
                    // $this->get('session')->clear();
                } else {
                    $this->get('session')->set('cart', $cart);
                }
            }
        }
        return $this->redirectToRoute('view_cart');
    }

    /**
     * @Route("/product_list/{category_id}", name="list_prdouct")
     */
    public function product(ProductRepository $productRepository, CategoryRepository $categoryRepository, $category_id = null)
    {
        if (empty($category_id)) {
            $products = $productRepository->findAll();
        } else {
            $cat = $categoryRepository->findOneBy(array('id' => $category_id));

            $products = $cat->getProducts();
        }

        return $this->render('store/product_list.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll()
        ]);
    }

    public static function customFormValidation($request)
    {
        $validator = Validation::createValidator();

        $input = [
            'firstname' => $request->request->get("firstname"),
            'lastname' => $request->request->get("lastname"),
            'contact' => $request->request->get("contact"),
            'address' => $request->request->get("address"),
            'city' => $request->request->get("city"),
            'pincode' => $request->request->get("pincode"),
        ];

        // Custom Validations
        $constraints = new Assert\Collection([
            'firstname' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'lastname' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'contact' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'address' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'city' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'pincode' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
        ]);

        $violations = $validator->validate($input, $constraints);

        if (count($violations) > 0) {

            $accessor = PropertyAccess::createPropertyAccessor();
            $errorMessages = [];

            foreach ($violations as $violation) {
                $accessor->setValue(
                    $errorMessages,
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }

            return array(
                'errors' => $errorMessages,
                'status' => false
            );
        }
        return array('status' => true);
    }

    /**
     * @Route("/my_account", name="my_account")
     */
    public function myAccount()
    {
        $user = $this->getUser();

        if ($user) {
            return $this->render('store/account.html.twig');
        }
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("account", name="")
     */

}