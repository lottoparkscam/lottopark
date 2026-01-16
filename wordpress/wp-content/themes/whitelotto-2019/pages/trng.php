<?php /* Template name: TRNG */ ?>
<?php
if (!defined('WPINC')) {
    die;
}
?>
<?php get_header(); ?>
<?php get_template_part('template-parts/header', 'small'); ?>
    <section class="container-fluid random-number-generator">
      <div class="container pt-0 pt-lg-5">
        <div class="circles-background">
          <h2 class="text-center text-lg-left">Random Number Generator - <br> Discover the New Era of Lottery Draws!</h2>
          <div class="row no-gutters justify-content-between align-items-center">
            <div class="col-lg-5 col-12">
              <p class="main-content text-center text-lg-left pl-5 pr-5 p-lg-0">
                Everybody knows traditional lottery drawing machines, which are full of numbered balls, each emerging to reveal the results of the draw. These machines are widely used around the world, but unfortunately many people are beginning to question their integrity.
                <br/><br/>
                But what if I told you that we have just made a revolution in the world of lotteries?
                <br/><br/>
                White Lotto is proud to present the TRNG! - True Random Number Generator. This unique solution is taking the world by surprise by combining an RNG system with an app that is not only proven fair, but also provides transparency to the draw by using Ethereum Smart Contracts. Certified by Gaming Labs International.
                <br/><br/>
                Imagine, now you can be sure that every result is fully random and transparent! It's not just a new technology, it's a real revolution that changes the rules of the game!
              </p>
            </div>
            <div class="col-lg-3 col-sm-5 col-12 white-box white-box-left">
              <h4>TRNG</h4>
              <p>
                True Random Generator server providing the fully random draw results.
              </p>
            </div>
            <div class="col-lg-3 col-sm-5 col-12 white-box white-box-right">
              <h4>PRNG</h4>
              <p>
                Fallback server in case the TRNG is not available for any reason.
              </p>
            </div>
          </div>
          <div class="text-center bg-white mt-lg-5 mt-5 mb-5 pl-3 pr-3 pb-5 rng-image-schema-container">
            <img
              src="<?php echo get_template_directory_uri(); ?>/images/rng.png"
              alt="Random Number Generator"
              class="rng-image-schema mt-5 w-100 p-3 p-lg-0"
            />
          </div>
          <h2 class="text-center text-lg-left pt-0 pt-lg-5 mt-0 mt-lg-5">How our RNG works:</h2>
          <div class="row no-gutters">
            <div class="col-lg-7 col-12">
              <p class="text-center text-lg-left pl-5 pr-5 p-lg-0">
                The TRNG algorithm is based on quantum physical processes which are fundamentally random. Subatomic particles' behavior on the quantum level is one of the few completely random processes in nature.
                <br/><br/>
                From the quantum physics point of view, light consists of elementary "particles" called photons. Photons exhibit a random behavior in a certain situation, which is very well suited to the generation of binary random numbers, is the transmission upon a semi-transparent mirror. The fact that a photon incident on such a component would be reflected or transmitted is intrinsically random and cannot be influenced by any external parameters.
              </p>
            </div>
            <div class="col-lg-5 col-12 text-right pt-lg-0 pt-5 text-center">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/rng_light.png"
                alt="Random Number Generator Scheme"
                class="rng-image-light w-100 p-3 p-lg-0"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="text-center bg-white mt-lg-5 mt-5 mb-5 pl-3 pr-3 pb-5 rng-image-schema-container">
        <img
          src="<?php echo get_template_directory_uri(); ?>/images/rng.png"
          alt="Random Number Generator"
          class="rng-image-schema mt-5 w-100 p-3 p-lg-0"
        />
      </div>
    </div>
  </div>
  <div class="container">
    <h2 class="text-center text-lg-left">Certificate</h2>
    <img src="<?php echo get_template_directory_uri(); ?>/images/cert.png" alt="Certificate" class="w-100">
  </div>
</section>
<section class="container section-center text-center">
  <h2>Ready to start?</h2>
  <div class="d-xl-flex justify-content-center text-center demo-wrapper">
    <a href="/contact" class="btn">GET IN TOUCH WITH US NOW!</a>
  </div>
</section>
<?php get_footer(); ?>
