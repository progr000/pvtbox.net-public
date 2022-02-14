<?php
use yii\helpers\Url;

return [
    'title' => 'Frequently Asked Questions',
    'html_text' => '
<div class="title">
    <h2>Frequently Asked Questions</h2>
</div>
<div class="faq-container">

    <div class="faq-chapters">
        <div class="faq-section"><a href="#section-general">General Questions</a></div>
        <ul>
            <li><a href="#q1">What is {APP_NAME}?</a></li>
            <li><a href="#q2">What else does {APP_NAME} offer?</a></li>
            <li><a href="#q3">What makes {APP_NAME} different from ordinary cloud services?</a></li>
            <li><a href="#q4">What about security? Do you use encryption?</a></li>
            <li><a href="#q5">Are there any limits to the speed and size of transferred files?</a></li>
            <li><a href="#q6">Which operating systems does {APP_NAME} work on?</a></li>
            <li><a href="#q7">What do I need to start working with {APP_NAME}?</a></li>
            <li><a href="#q8">Why can I see {APP_NAME} connecting to its own servers; signal server, for example? You said this was a peer-2-peer service without a public cloud.</a></li>
            <li><a href="#q9">Who is {APP_NAME} useful for?</a></li>
            <li><a href="#q10">I’ve found my copyrighted content on public links. How can I delete it?</a></li>
            <li><a href="#q11">How do you process data requests?</a></li>

        <div class="faq-section"><a href="#section-desktop-mobile">Desktop & mobile application questions</a></div>
        <ul>
            <li><a href="#q12">The app won’t run from my smartphone and/or computer. What can I do?</a></li>
            <li><a href="#q13">I’ve installed the app. How can I transfer files to my other devices or to friends/colleagues?</a></li>
            <li><a href="#q14">What’s a ‘collaboration folder’? How does it work?</a></li>
            <li><a href="#q15">Can I set up a password for downloading files from an https:// link, or a self-destruct timer on the link itself?</a></li>
            <li><a href="#q16">What’s the difference between ‘backup’ and ‘backup-less’ mode on the desktop application?</a></li>
            <li><a href="#q17">Does {APP_NAME} have version history support for files?</a></li>
            <li><a href="#q18">How can I restore a previous version of a file?</a></li>
            <li><a href="#q19">How can I save disk space on my computer while using {APP_NAME}?</a></li>
            <li><a href="#q20">There isn’t a lot of space on my smartphone. How can I receive files from my computer and at the same time save disk space?</a></li>
            <li><a href="#q21">I want a certain file/folder on my smartphone to always be the currentversion, and for it to always be available (downloaded from other devices). How can this be done?</a></li>
            <li><a href="#q22">How can I transfer a photo & video gallery from a smartphone to a computer?</a></li>
            <li><a href="#q23">I no longer have access to a device which {APP_NAME} is installed on. Can I wipe information from the sync folder remotely?</a></li>
            <li><a href="#q24">How can I view IP addresses and devices connected to my account?</a></li>
            <li><a href="#q25">Can I delete my {APP_NAME} account?</a></li>
        </ul>

        <div class="faq-section"><a href="#section-business">Business user questions</a></div>
        <ul>
            <li><a href="#q26">How does the <span class="faq-bold">‘Business’</span> tariff differ from the <span class="faq-bold">‘Professional’</span> tariff?</a></li>
            <li><a href="#q27">I’ve just got a new co-worker. How can I invite him/her for joint work on our documents?</a></li>
            <li><a href="#q28">I have a ‘Business Administrator’ account. How can I monitor my co-workers’ actions log?</a></li>
            <li><a href="#q29">I need 100% (or thereabouts) uptime for our collaboration folder. How can this be done?</a></li>
            <li><a href="#q30">I have a problem with using {APP_NAME}. Who can I get in touch with?</a></li>
        </ul>
    </div>

    <p>&nbsp;</p>
    <div class="faq-section-general">
        <h3 class="faq-section-header"><a class="faq-section-anchor" name="section-general" href="#section-general"></a>General Questions</h3>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q1" href="#q1"></a>
                What is {APP_NAME}?
            </div>
            <div class="faq-answer">
                {APP_NAME} is an app for smartphones, tablet and PCs that
                enables quick transfer and synchronization of files directly
                between your devices. Your files are saved only on your devices and nowhere else.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q2" href="#q2"></a>
                What else does {APP_NAME} offer?
            </div>
            <div class="faq-answer">
                As well as file transfer using end-2-end encryption, {APP_NAME} allows you to:
                <p></p>
                - Sync your data automatically or by using a selective mode;
                <p></p>
                - Safely carry out joint work on files with colleagues, friends, etc.;
                <p></p>
                - Share files via https:// link with people who do not have {APP_NAME} installed.
                <p></p>
                - Remotely wipe data on any device should you lose access to it;
                <p></p>
                - Easily create your own private cloud from your trusted devices.
                <p></p>
                All you need is to have {APP_NAME} installed on each.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q3" href="#q3"></a>
                What makes {APP_NAME} different from ordinary cloud services?
            </div>
            <div class="faq-answer">
                Whenever you use an ordinary cloud service, anything you upload to a public cloud stays
                there for a long time. Even files you have deleted can theoretically be accessed by others.
                <p></p>
                The concept of {APP_NAME} is fundamentally different. When you transfer files via {APP_NAME},
                they are stored <span class="faq-bold">ONLY</span> on your devices and nowhere else. We do not store your files anywhere
                and do not have any kind of access to them!
                <p></p>
                It’s also worth pointing out that thanks to the absence of storage on tertiary servers,
                you can transfer and receive files more quickly. Data is carried from device to device via
                the shortest peer-2-peer route. It’s always quicker to fly directly than taking a roundabout route!
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q4" href="#q4"></a>
                What about security? Do you use encryption?
            </div>
            <div class="faq-answer">
                We put special emphasis on security and confidentiality! An open source code - WebRTC - is
                used for file-transfer traffic between devices.
                <br />
                The source code WebRTC is open for viewing, analysis and adjustment, which means you can be certain about
                the absence of any vulnerabilities and undesirable software (for instance, programs that secretly track
                the user’s activity). What’s more, anyone can help to further develop it.
                <p></p>
                All traffic is protected end-2-end by the encryption algorithm DTLS-SRTP, which is immune to MITM attacks.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q5" href="#q5"></a>
                Are there any limits to the speed and size of transferred files?
            </div>
            <div class="faq-answer">
                No, it’s all limitless. You can transfer files of any size*: 1 TB or 1KB - there’s no difference.
                You are limited only by your network’s bandwidth and your device’s file storage capacity.
                <p></p>
                <span class="faq-small faq-gray">*There are limits on transfer for the <span class="faq-bold">‘Free’</span> tariff: 1GB maximum file size and no more than 3 links per 24 hours.</span>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q6" href="#q6"></a>
                Which operating systems does {APP_NAME} work on?
            </div>
            <div class="faq-answer">
                {APP_NAME} works on most popular desktop and mobile operating systems: Windows, OS X, Linux, Android, iOS.
                A protected Web panel is also available so you can manage your private cloud conveniently via your browser.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q7" href="#q7"></a>
                What do I need to start working with {APP_NAME}?
            </div>
            <div class="faq-answer">
                To get started, you need to register, then download and install the mobile or desktop application.
                After that, {APP_NAME} does all the rest!
                <p></p>
                Once you have the app installed, you can add files to the directory for syncing,
                then share them with your colleagues and friends. If you add more than one device to your account,
                your content will automatically be synced across your devices.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q8" href="#q8"></a>
                Why can I see {APP_NAME} connecting to its own servers; signal server, for example? You said this was a peer-2-peer service without a public cloud.
            </div>
            <div class="faq-answer">
                ‘Peer-2-peer’ connection implies a certain signal server that ’connects’ users’ devices. Additionally,
                a user’s devices might be part of a local network with complex topology or be behind a firewall.
                In such cases, connection to these devices is only possible via a turn (proxy) server.
                But one thing stays the same: <span class="faq-bold">Your traffic is protected by end-2-end encryption and your content
                is saved only on your devices, and nowhere else.</span>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q9" href="#q9"></a>
                Who is {APP_NAME} useful for?
            </div>
            <div class="faq-answer">
                For any individual or business who cares about the security and confidentiality of their data
                and is accustomed to being the sole and complete owner of their own information.
                <p></p>
                {APP_NAME} is useful for anyone who wants to transfer data from A to B quickly and safely.
                It might be a couple dozen photos, or hundred-terabyte files located at opposite ends of the globe.
                <p></p>
                <span class="faq-small faq-gray">*There are limits on transfer for the <span class="faq-bold">‘Free’</span> tariff: 1GB maximum file size
                and no more than 3 links per 24 hours.</span>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q10" href="#q10"></a>
                I’ve found my copyrighted content on public links. How can I delete it?
            </div>
            <div class="faq-answer">
                {APP_NAME} allows information to be transferred between users via automatic syncing of devices, collaboration folders, or public https:// links. In the case of the former two, we will have no knowledge of what information gets transferred. However, if information is passed on via https:// links to be accessed by an unlimited number of viewers and is unprotected; by password, for example, then it will become public. If we receive a validated complaint about such a link, we can deactivate it at the service level.
                <p></p>
                Please note that we will ask you for all documents that confirm your ownership of intellectual property.
                <br>
                Queries can be sent to: <a href="mailto:abuse@pvtbox.net">abuse@pvtbox.net</a>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q11" href="#q11"></a>
                How do you process data requests?
            </div>
            <div class="faq-answer">
                Thanks to the end-2-end encryption imbedded in our apps, no one can intercept or decrypt your data.
                Therefore, in terms of technology, it is impossible for files that belong to you to be transferred to anybody else.
                <br />
                In all other cases, we need a court order to disclose any portion of the small amount of information we own.
            </div>
        </div>
    </div>

    <div class="section-desktop-mobile">
        <h3 class="faq-section-header"><a class="faq-section-anchor" name="section-desktop-mobile" href="#section-desktop-mobile"></a>Desktop & mobile application questions</h3>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q12" href="#q12"></a>
                The app won’t run from my smartphone and/or computer. What can I do?
            </div>
            <div class="faq-answer">
                Make sure your computer or smartphone has the required minimum version of a compatible operating system.
                For Windows - Windows 7 or later, Mac - OS X 10.11 or later, Android - 5.0 or later, IOS - 10.0 or later.
                Correct operation on older versions cannot be guaranteed.
                <p></p>
                Make sure your device has enough free space for the app to run and operate, and also that any defensive
                software (for instance, antivirus, firewalls etc.) will allow {APP_NAME} to run.
                <p></p>
                Get in touch with us if you need any help and we will do our best to solve your problem.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q13" href="#q13"></a>
                I’ve installed the app. How can I transfer files to my other devices or to friends/colleagues?
            </div>
            <div class="faq-answer">
                Once you have the app installed, there are three modes for transferring files to other devices:
                <br />
                - Automatic file syncing across your personal devices. This is the default setting*.
                <br />
                - Transfer via a shared collaboration folder. A shared folder allows users to work jointly on documents,
                as well as to add files. To do this, you need to choose a folder and invite a friend to join it*.
                <br />
                - Transfer via https:// link. Anyone can download the file from a browser.
                <p></p>
                <span class="faq-small faq-gray">*Not available with the <span class="faq-bold">‘Free’</span> tariff.</span>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q14" href="#q14"></a>
                What’s a ‘collaboration folder’? How does it work?
            </div>
            <div class="faq-answer">
                A collaboration folder is a folder which is commonly available to you and your friends/colleagues.
                Any content uploaded to this folder by any of its collaborators will also be displayed to all other
                participants to the folder.
                <p></p>
                To invite a friend or colleague for collaboration, choose a folder via the Web panel or via
                the mobile app, right click on it, then <span class="faq-italic">‘Share → Collaborations settings → Add colleague’</span>.
                ’Enter your colleague’s email address in this field, then click ’Invite’.
                An email with an invitation will then be sent to the address given.
                Once your friend/colleague clicks on the link in the email,
                they will become a collaborator and have access to the collaboration folder.
                <p></p>
                Of course, they will also need to have the {APP_NAME} app installed.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q15" href="#q15"></a>
                Can I set up a password for downloading files from an https:// link, or a self-destruct timer on the link itself?
            </div>
            <div class="faq-answer">
                Yes. These settings are available when you create a link via the mobile app or Web panel.
                <p></p>
                <span class="faq-small faq-gray">*Not available with the <span class="faq-bold">‘Free’</span> tariff.</span>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q16" href="#q16"></a>
                What’s the difference between ‘backup’ and ‘backup-less’ mode on the desktop application?
            </div>
            <div class="faq-answer">
                On running the desktop app for the first time, the system offers you to choose a certain setting:
                ‘Let the system make backups of your files’. If you allow the system to make a reserve copy of your files,
                you will be able to restore any file for up to 30 days after its deletion. Naturally,
                this setting takes up space on your hard drive.
                <p></p>
                If you operate the system in backup-less mode, it is not possible to restore deleted files.
                The system will only work for transferring files.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q17" href="#q17"></a>
                Does {APP_NAME} have version history support for files?
            </div>
            <div class="faq-answer">
                Yes. You can restore any version of a particular file from the past 30 days.
                This is useful if, for example, you have deleted a file accidentally and want to get it back,
                or if a file\'s contents have changed and you want to restore a previous version.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q18" href="#q18"></a>
                How can I restore a previous version of a file?
            </div>
            <div class="faq-answer">
                You can set this up via the Web panel. Go onto the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a>,
                enter your account and right click the relevant file, then choose <span class="faq-italic">‘File versions’</span>.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q19" href="#q19"></a>
                How can I save disk space on my computer while using {APP_NAME}?
            </div>
            <div class="faq-answer">
                When your computer syncs to your other devices, it downloads all available files in
                collaboration folders on other devices. It might turn out that there is not enough space on your hard drive.
                You can solve this problem by using the function <span class="faq-italic">‘Selective sync’</span>.
                Go into <span class="faq-italic">‘Settings → Selective sync’</span>, then remove the tick from the folder you don’t want to sync with your computer.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q20" href="#q20"></a>
                There isn’t a lot of space on my smartphone. How can I receive files from my computer and at the same time save disk space?
            </div>
            <div class="faq-answer">
                As a default, all files received by your mobile device in sync mode will be displayed as ‘placeholders’.
                ‘Placeholders’ take up practically no space, but if you ‘tap’ a placeholder, the file will automatically
                be downloaded from a device available online. Additionally, in ‘Settings’ there is the option
                <span class="faq-italic">‘Automatically download media files < 10MB’</span>. Turning this off will help save space on your mobile.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q21" href="#q21"></a>
                I want a certain file/folder on my smartphone to always be the currentversion, and for it to always be available (downloaded from other devices). How can this be done?
            </div>
            <div class="faq-answer">
                If you activate the setting <span class="faq-italic">‘Create offline copy’</span> for a file or folder,
                then backups will be created for it and your smartphone will automatically look for new versions.
                You’ll always have the latest version of the file on your phone.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q22" href="#q22"></a>
                How can I transfer a photo & video gallery from a smartphone to a computer?
            </div>
            <div class="faq-answer">
                In the mobile app’s settings choose the option ‘Automatically upload photos and videos from camera’
                and simultaneously run {APP_NAME} on your computer.
                The gallery from your smartphone will be synced to your computer.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q23" href="#q23"></a>
                I no longer have access to a device which {APP_NAME} is installed on. Can I wipe information from the sync folder remotely?
            </div>
            <div class="faq-answer">
                Yes. You can wipe information remotely and log out of the account on that device.
                To do this, log into your account on the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a>, then go to the option
                <span class="faq-italic">‘My devices → Manage</span>, and select <span class="faq-italic">‘Log out & Remote wipe’</span>.
                The data will disappear as soon as the device appears online.
                <p></p>
                <span class="faq-small faq-gray">*Not available with the <span class="faq-bold">‘Free’</span> tariff.</span>
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q24" href="#q24"></a>
                How can I view IP addresses and devices connected to my account?
            </div>
            <div class="faq-answer">
                Log into your account on the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a>, then go to the option
                <span class="faq-italic">‘My devices → Manage’</span>, and you will see a list of IP addresses and IDs of devices
                that {APP_NAME} has been run from.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q25" href="#q25"></a>
                Can I delete my {APP_NAME} account?
            </div>
            <div class="faq-answer">
                Yes. You can do this via <span class="faq-italic">‘Web panel → Settings’</span>. If you delete your account,
                you will automatically be logged out on all your devices and your emails and file metadata
                will be permanently and irretrievably deleted from our servers.
                Files stored locally on your devices in the {APP_NAME} sync folder will not be deleted.
                <p></p>
                Should you delete your account, it will not be possible to restore it.
            </div>
        </div>
    </div>

    <div class="section-business">
        <h3 class="faq-section-header"><a class="faq-section-anchor" name="section-business" href="#section-business"></a>Business user questions</h3>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q26" href="#q26"></a>
                How does the <span class="faq-bold">‘Business’</span> tariff differ from the <span class="faq-bold">‘Professional’</span> tariff?
            </div>
            <div class="faq-answer">
                The <span class="faq-bold">‘Business’</span> tariff is designed for the specific needs of businesses and has additional
                functions as compared to the <span class="faq-bold">‘Professional’</span> tariff.
                <p></p>
                In the <span class="faq-bold">‘Business’</span> tariff, there is an <span class="faq-italic">‘Admin panel’</span> for business administrators.
                Via this panel, you can add co-workers, grant them the right to access collaboration folders,
                and also monitor a log of actions carried out by your co-workers; what they have worked on and when.
                This allows you in turn to additionally control the working process.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q27" href="#q27"></a>
                I’ve just got a new co-worker. How can I invite him/her for joint work on our documents?
            </div>
            <div class="faq-answer">
                As a business administrator, go to <span class="faq-italic">‘Admin panel → Collaboration settings → Add license’</span>.
                You will be asked to choose the number of required additional licenses and can them to those already existing.
                Once you have done this, you can invite the new co-worker.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q28" href="#q28"></a>
                I have a ‘Business Administrator’ account. How can I monitor my co-workers’ actions log?
            </div>
            <div class="faq-answer">
                As a business administrator, if you go to the tab <span class="faq-italic">‘Admin panel → Reports’</span>,
                you will be able to view the actions log for every co-worker in your joint collaboration folder.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q29" href="#q29"></a>
                I need 100% (or thereabouts) uptime for our collaboration folder. How can this be done?
            </div>
            <div class="faq-answer">
                You can always set up {APP_NAME} on a personal or hired server with a high level of uptime.
                If you do this, you and your co-workers’ collaboration folder will always be online,
                and as a result any changes made by you or your co-workers will be
                instantaneously uploaded to your collaboration folder.
            </div>
        </div>

        <div class="faq-question-answer">
            <div class="faq-question">
                <a class="faq-anchor" name="q30" href="#q30"></a>
                I have a problem with using {APP_NAME}. Who can I get in touch with?
            </div>
            <div class="faq-answer">
                You can leave a question or describe your problem in the <a href="' . Url::to(['/support'], CREATE_ABSOLUTE_URL) . '">form</a>,
                or send us an email to <a href="mailto:support@pvtbox.net">support@pvtbox.net</a>
                <p></p>
                We will try to get back to you as quickly as possible.
            </div>
        </div>

    </div>

</div>',
];