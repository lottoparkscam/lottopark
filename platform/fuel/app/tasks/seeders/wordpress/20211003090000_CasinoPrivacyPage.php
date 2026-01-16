<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;
use Helper_Route;

final class CasinoPrivacyPage extends AbstractPage
{
    protected const TYPE = 'parent';
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'slug' => Helper_Route::CASINO_PRIVACY_POLICY,
            'title' => 'Privacy Policy',
            'body' => '
                <p>This Policy has been drafted to explain how [whitelabelCompany] is processing Personal Data and how the data protection principles are being applied. If you have any questions or concerns regarding the privacy policy or the way We are processing the data, please do not hesitate to contact our Data Privacy Officer: [whitelabelSupportEmail]</p>
                <p>Please keep in mind that by registering at <strong>[whitelabelCasinoDomain], you give us your consent to the processing of your Personal Data in a manner described below</strong>. The data collected is necessary to manage the customer relationship at [whitelabelCasinoDomain]</p>
                <p><strong>1. Definitions</strong></p>
                <p><strong>‘’Personal Data’’ or “Identification Data“</strong> mean any piece of data or information that relates to a certain concrete natural person which is in connection with this information identified or which can be directly or indirectly identified on the basis of such information as your name, surname, date of birth, e-mail address, phone number, identification number, login details, any information regarding the transactions, time of access, credit card number, banking details, location data, an online identifier or to one or more factors specific to the physical and address.<br><strong>“Processing“</strong> means any operation or set of operations which is performed on Personal Data or on sets of Personal Data, whether or not by automated means, such as collection, recording, organisation, structuring, storage, adaptation or alteration, retrieval, consultation, use, disclosure by transmission, dissemination or otherwise making available, alignment or combination, restriction, erasure or destruction.<br><strong>&#8220;[whitelabelDomain]&#8221;, “Casino”, ‘’We’’, ‘’Us’’, ‘’Our’’ or “Controller”</strong> means [whitelabelCompanyName], a limited liability company registered in Curacao with company registered address, [whitelabelCompanyAddress].</p>
                <p><strong>‘’Website’’</strong> means our domain: [whitelabelDomain], if not otherwise mentioned.</p>
                <p><strong>2. Scope of Processing</strong></p>
                <p>We are collecting your data in order to maintain the customer relationship with you. We also have legal obligations to gather and process your Personal Data. The following data is being processed:</p>
                <ul>
                <li>Any of the information that you provide to us when filling in the forms on our account registration pages, as well as any other data that you further submit via the Website or email (e.g. first and last name, date of birth, email address, phone number);</li>
                <li>Correspondence made with us via the Website, email, web chat or through other means of communication;</li>
                <li>All Player Account transaction history, whether this takes place via the Website(s) or via other means of communication;</li>
                <li>Website logins and their details, including traffic data, GeoIP location data, browser/device data, weblogs, activity logs and other traffic information recorded in our system;</li>
                <li>Documents and proofs reasonably requested by us to verify your account, to process deposits or withdrawals and to conduct anti-fraud or anti-money laundering checks (on our own initiative or as required by applicable legislation). Such proofs may include passport scans, payment slips, bank statements, etc.</li>
                <li>Survey participations or any other customer assessments that We may carry out from time to time.</li>
                </ul>
                <p><strong>3. Purpose of Processing</strong></p>
                <ul>
                <li>Processing your bets and transactions. This includes your use of credit card and online payment systems;</li>
                <li>Providing you with gaming and other ancillary services that you seek from our Website;</li>
                <li>Rendering customer support, such as assistance with setting up and managing your account;</li>
                <li>Identifying and performing the necessary verification checks;</li>
                <li>Providing registered players with information about our promotional offers, or providing promotional information from our selected business partners, associates and affiliates (only if players specifically consented to receiving such marketing material);</li>
                <li>Complying with legal responsibilities, including complying with anti-money laundering (AML) and combating the financing of terrorism (CFT) laws;</li>
                <li>Monitoring and investigating transactions for the purposes of preventing fraud, terms abuse, money laundering and other illegal or irregular gaming activities;</li>
                <li>Analysing customer trends through market study assessments (participation in surveys is not obligatory and you can always choose not to take part);</li>
                <li>Conducting research and statistical analysis of aggregated data;</li>
                <li>Sending you marketing communications regarding products, services and promotions. This may include information about product and services from our business partners, such as casino game providers.</li>
                </ul>
                <p><strong>4. Security and Storage of Personal Data</strong></p>
                <p>The Personal Data is stored in a manner that ensures appropriate safety measures.<br>Consequently, Casino endeavors to protect your personal information and respect your privacy in accordance with best business practices and applicable regulations. Being committed to providing secure services to players, and We will take all reasonable precautions to ensure that all the data that you have submitted to us remains safe.</p>
                <p>Player Accounts can only be accessed with the player’s unique ID and password. You may also set up two-factor authentication (2FA) as additional protection from unauthorised use of your account. You are responsible for keeping your login information confidential and making sure it cannot be accessed by another person.</p>
                <p>Casino will keep your Personal Data for maximum of ten years after the date of your last activity on the Website. This time has been determined in order to comply with the purposes set out in this policy and relevant legal and regulatory obligations.</p>
                <p>As stated under our Terms and Conditions both, you and the Casino can decide to have your Player Account closed at any time. Following closure of your account, We will retain your Personal Data on record for as long as required by law. This data shall only be used should it be required by competent authorities in cases of enquiries regarding financial and fiscal records, fraud, money laundering or investigations into any other illegal activity.</p>
                <p>You are to note that due to anti-money laundering regulations in licensed gaming jurisdictions in the European Union, We are obliged to retain Personal Data of players submitted during registration and any data passed on during the operative period of a Player Account for a minimum of five years from last player transaction or account closure. Therefore, requests for erasure prior to the lapse of this period cannot be approved.</p>
                <p><strong>5. Your rights</strong></p>
                <p>In connection with the processing of your Personal Data you have the following rights:</p>
                <ul>
                <li><strong>the right of access</strong>, you have the right to request Casino for copies of your Personal Data. We may charge a reasonable fee based on the type of your request;</li>
                <li><strong>the right to rectification of your Personal Data</strong>, you have the right to request that Casino to correct any information you believe is inaccurate. You also have the right to request Casino to complete the information you believe is incomplete;</li>
                <li><strong>the right to withdraw this consent</strong> to the processing of your Personal Data;</li>
                <li><strong>the right to be forgotten, i.e., the right to have the Controller erase your Personal Data</strong> if any of the following cases arises:<br>&#8211; your Personal Data are no longer necessary for the above specified purposes;<br>&#8211; you withdraw this consent to processing of your Personal Data;<br>&#8211; your Personal Data are being processed unlawfully by the Controller;<br>&#8211; your Personal Data must be erased in order to fulfil the Controller´s legal obligation;</li>
                <li>the right to restriction of the processing of your Personal Data if any of the following cases arises:<br>&#8211; you contest the accuracy of the processed Personal Data;<br>&#8211; the processing of your Personal Data is unlawful;<br>&#8211; the Controller no longer needs your Personal Data for the given purposes;</li>
                <li><strong>the right to data portability</strong>, i.e. the right to receive your Personal Data which are processed by the Controller with the use of technical means (stores in an electronic register etc.) in a structured, machine-readable format.</li>
                </ul>
                <p><strong>6. Releasing data to third parties and outside of the EEA</strong></p>
                <p>We do not sell or rent your Personal Data to third parties.<br>We may disclose your personal information if required by law, regulation, or other legal subpoena or warrant. We may also disclose your personal information to a regulatory or law enforcement agency if We believe it to be necessary to protect the legitimate interests of the Casino, its customers or any third party.</p>
                <p>Personal Data will only be disclosed to third parties in the following cases:</p>
                <ul>
                <li>Where We are required to do so by law;</li>
                <li>If the Website needs to share data with its payment processors to facilitate payment transactions in accordance with their privacy policies;</li>
                <li>To comply with our legal and regulatory duties and responsibilities to the relevant licensing and regulatory authorities as well as all duties and responsibilities owed under any other applicable legislation and to any other applicable regulators in other jurisdictions;</li>
                <li>When Casino believes that disclosure is necessary to protect the Casino’s or the player’s safety, or the safety of others, investigate fraud, or respond to a government request;</li>
                <li>If our marketing service providers require the data to carry out their tasks;</li>
                <li>To any other third party with the player’s prior consent to do so.<br>&#8211; We use third-party data processors to process limited Personal Data on our behalf. Such service providers support the Website, especially relating to hosting and operating the Websites, marketing, analytics, improving the Websites, and sending email newsletters. We shall ensure that the transfer of the Personal Data to the recipient is compliant with applicable Data Protection Legislation and that the same obligations are imposed on the processor as is imposed on us under the respective Services Agreement.<br>&#8211; Our Websites may also include social media features (e.g. “share” or “like” buttons). Such features are provided by third-party social media platforms such as Facebook. Where data is collected this way, its processing is governed by the privacy policy of the respective social media platforms.</li>
                </ul>
                <p>We use third-party data processors to process limited Personal Data on our behalf. Such service providers support the Website, especially relating to hosting and operating the websites, marketing, analytics, improving the Websites, and sending email newsletters. We shall ensure that the transfer of the Personal Data to the recipient is compliant with applicable Data Protection Legislation and that the same obligations are imposed on the processor as is imposed on us under the respective Services Agreement.</p>
                <p>Our Websites may also include social media features (e.g. “share” or “like” buttons). Such features are provided by third-party social media platforms such as Facebook. Where data is collected this way, its processing is governed by the privacy policy of the respective social media platforms.</p>
                <p>In addition to the above, We may also release Personal Data if We acquire any new businesses. Should the Casino undergo any changes to its structure such as a merger, acquisition by another company or a partial acquisition, it is most likely that our customers’ Personal Data will be included within the sale or transfer. We will, as part of our Policy, inform our players by email prior to affecting such transfer of Personal Data.<br>Please note our content may link to third party Websites to provide relevant references. We are not responsible for such external content, which may contain separate privacy policies and data processing disclosures.</p>
                <p>Insofar as personal data of persons from the European Union, towards which our services may be targeted, are concerned, the following rules shall apply as well:</p>
                <p>We only transfer your Personal Data outside of the EEA where:</p>
                <ul>
                <li>You have given your explicit consent, or</li>
                <li>It necessary for us to set up or fulfil a contract you have entered into with us; or</li>
                <li>To comply with a legal duty or obligation</li>
                </ul>
                <p>In the event of transferring data outside of EEA within our partners or within xxxxxx. organization, We make sure that your Personal Data will be transferred only if there is a genuine reason to do so and necessary safeguards are in place. The most common safeguards are following:</p>
                <ul>
                <li>The country that is receiving your Personal Data has been found by the European Commission to offer the same level of protection as the EEA.</li>
                <li>The contracts used require same level of protection as the standards within the EEA.</li>
                </ul>
                <p><strong>7. Cookies</strong></p>
                <p>Cookies are text files placed on your computer to collect standard Internet log information and visitor behavior information. When you visit Casino, We collect information from you automatically through cookies and similar technologies.<br>You can set your browser not to accept cookies, and the above Website tells you how to remove cookies from your browser. However, in a few cases, some of our Website features may not function as a result.<br>To learn more about cookies, you may request additional information from our customer support.</p>
                <p><strong>8. Updates to our Privacy Policy</strong></p>
                <p>The Policy might undergo changes time to time. Please ensure that you will be reviewing this page to any updates. This Policy has been updated on March 01 2022<br>Casino will take reasonable efforts to inform you about any material changes. The contact will be made, in advance, in form of email, notice on our Website, or other agreed form of communication.<br>We will not enforce material changes to the Privacy Policy without your express consent. If you decline to accept the changes to the Privacy Policy, or otherwise do not accept the changes within the time period, We may not be able to continue to provide some or all products and services.</p>
                <p><strong>9. Contact Us</strong></p>
                <p>If you have any questions regarding the policy, the data We hold on you or would like to exercise your rights related to this data, please do not hesitate to contact our Data Privacy Officer: [whitelabelSupportEmail]</p>
                <p><strong>10. Filing a complaint against us</strong></p>
                <p>If you wish to file a complaint against us or if you feel that We have not addressed your concerns in a satisfactory manner, you may contact the relevant local authority overseeing personal data protection in your country.</p>

            ',
        ],
    ];
}
