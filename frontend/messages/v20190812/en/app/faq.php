<?php
use yii\helpers\Url;

return [
    'title' => 'Frequently Asked Questions',
    'html_text' => '
    <h1 class="centered">Frequently Asked Questions</h1>


<!-- questions sections -->
<div class="questions">
    <div class="questions-section">
        <a class="question-chapter js-scroll-to" href="#ch1">General Questions</a>
        <ul>
            <li><a class="js-scroll-to" href="#q1">What is {APP_NAME}?</a></li>
            <li><a class="js-scroll-to" href="#q2">What else does {APP_NAME} offer?</a></li>
            <li><a class="js-scroll-to" href="#q3">What makes {APP_NAME} different from ordinary cloud services?</a></li>
            <li><a class="js-scroll-to" href="#q4">What about security? Do you use encryption?</a></li>
            <li><a class="js-scroll-to" href="#q5">Are there any limits to the speed and size of transferred files?</a></li>
            <li><a class="js-scroll-to" href="#q6">Which operating systems does {APP_NAME} work on?</a></li>
            <li><a class="js-scroll-to" href="#q7">What do I need to start working with {APP_NAME}?</a></li>
            <li><a class="js-scroll-to" href="#q8">Why can I see {APP_NAME} connecting to its own servers; signal server, for example? You said this was a peer-2-peer service without a public cloud.</a></li>
            <li><a class="js-scroll-to" href="#q9">Who is {APP_NAME} useful for?</a></li>
            <li><a class="js-scroll-to" href="#q10">I’ve found my copyrighted content on public links. How can I delete it?</a></li>
            <li><a class="js-scroll-to" href="#q11">How do you process data requests?</a></li>
        </ul>
    </div>
    <div class="questions-section">
        <a class="question-chapter js-scroll-to" href="#ch2">Desktop & mobile application questions</a>
        <ul>
            <li><a class="js-scroll-to" href="#q12">The app won’t run from my smartphone and/or computer. What can I do?</a></li>
            <li><a class="js-scroll-to" href="#q13">I’ve installed the app. How can I transfer files to my other devices or to friends/colleagues?</a></li>
            <li><a class="js-scroll-to" href="#q14">What’s a ‘collaboration folder’? How does it work?</a></li>
            <li><a class="js-scroll-to" href="#q15">Can I set up a password for downloading files from an https:// link, or a self-destruct timer on the link itself?</a></li>
            <li><a class="js-scroll-to" href="#q16">What’s the difference between ‘backup’ and ‘backup-less’ mode on the desktop application?</a></li>
            <li><a class="js-scroll-to" href="#q17">Does {APP_NAME} have version history support for files?</a></li>
            <li><a class="js-scroll-to" href="#q18">How can I restore a previous version of a file?</a></li>
            <li><a class="js-scroll-to" href="#q19">How can I save disk space on my computer while using {APP_NAME}?</a></li>
            <li><a class="js-scroll-to" href="#q20">There isn’t a lot of space on my smartphone. How can I receive files from my computer and at the same time save disk space?</a></li>
            <li><a class="js-scroll-to" href="#q21">I want a certain file/folder on my smartphone to always be the currentversion, and for it to always be available (downloaded from other devices). How can this be done?</a></li>
            <li><a class="js-scroll-to" href="#q22">How can I transfer a photo & video gallery from a smartphone to a computer?</a></li>
            <li><a class="js-scroll-to" href="#q23">I no longer have access to a device which {APP_NAME} is installed on. Can I wipe information from the sync folder remotely?</a></li>
            <li><a class="js-scroll-to" href="#q24">How can I view IP addresses and devices connected to my account?</a></li>
            <li><a class="js-scroll-to" href="#q25">Can I delete my {APP_NAME} account?</a></li>
        </ul>
    </div>
    <div class="questions-section">
        <a class="question-chapter js-scroll-to" href="#ch3">Business user questions</a>
        <ul>
            <li><a class="js-scroll-to" href="#q26">How does the <span class="faq-bold">‘Business’</span> tariff differ from the <span class="faq-bold">‘Professional’</span> tariff?</a></li>
            <li><a class="js-scroll-to" href="#q27">I’ve just got a new co-worker. How can I invite him/her for joint work on our documents?</a></li>
            <li><a class="js-scroll-to" href="#q28">I have a ‘Business Administrator’ account. How can I monitor my ’co-workers’ actions log?</a></li>
            <li><a class="js-scroll-to" href="#q29">I need 100% (or thereabouts) uptime for our collaboration folder. How can this be done?</a></li>
            <li><a class="js-scroll-to" href="#q30">I have a problem with using {APP_NAME}. Who can I get in touch with?</a></li>
        </ul>
    </div>
</div>

<!-- answers sections -->
<div class="answers">
    <div class="answers-section">
        <div class="answers-chapter" id="ch1">General Questions</div>
        <div class="answers-item" id="q1">
            <div class="answers-item__question">What is {APP_NAME}?</div>
            <div class="answers-item__answer">
                <p>{APP_NAME} is an app for smartphones, tablet and PCs that enables quick transfer and synchronization of files directly between your devices. Your files are saved only on your devices and nowhere else.</p>
            </div>
        </div>
        <div class="answers-item" id="q2">
            <div class="answers-item__question">What else does {APP_NAME} offer?</div>
            <div class="answers-item__answer">
                <p>As well as file transfer using end-2-end encryption, {APP_NAME} allows you to:</p>
                <ul>
                    <li>Sync your data automatically or by using a selective mode;</li>
                    <li>Safely carry out joint work on files with colleagues, friends, etc.;</li>
                    <li>Share files via https:link with people who do not have {APP_NAME} installed.</li>
                    <li>Remotely wipe data on any device should you lose access to it;</li>
                    <li>Easily create your own private cloud from your trusted devices.</li>
                </ul>
                <p>All you need is to have {APP_NAME} installed on each.</p>
            </div>
        </div>
        <div class="answers-item" id="q3">
            <div class="answers-item__question">What makes {APP_NAME} different from ordinary cloud services?</div>
            <div class="answers-item__answer">
                <p>Whenever you use an ordinary cloud service, anything you upload to a public cloud stays there for a long time. Even files you have deleted can theoretically be accessed by others.</p>
                <p>The concept of {APP_NAME} is fundamentally different. When you transfer files via {APP_NAME}, they are stored ONLY on your devices and nowhere else. We do not store your files anywhere and do not have any kind of access to them!</p>
                <p>It’s also worth pointing out that thanks to the absence of storage on tertiary servers, you can transfer and receive files more quickly. Data is carried from device to device via the shortest peer-2-peer route. It’s always quicker to fly directly than taking a roundabout route!</p>
            </div>
        </div>
        <div class="answers-item" id="q4">
            <div class="answers-item__question">What about security? Do you use encryption?</div>
            <div class="answers-item__answer">
                <p>We put special emphasis on security and confidentiality! An open source code - WebRTC - is used for file-transfer traffic between devices.</p>
                <p>The source code WebRTC is open for viewing, analysis and adjustment, which means you can be certain about the absence of any vulnerabilities and undesirable software (for instance, programs that secretly track the user’s activity). What’s more, anyone can help to further develop it.</p>
                <p>All traffic is protected end-2-end by the encryption algorithm DTLS-SRTP, which is immune to MITM attacks.</p>
            </div>
        </div>
        <div class="answers-item" id="q5">
            <div class="answers-item__question">Are there any limits to the speed and size of transferred files?</div>
            <div class="answers-item__answer">
                <p>No, it’s all limitless. You can transfer files of any size*: 1 TB or 1KB - there’s no difference. You are limited only by your network’s bandwidth and your device’s file storage capacity.</p>
                <p class="footnote">*There are limits on transfer for the <span class="faq-bold">‘Free’</span> tariff: 1GB maximum file size and no more than 3 links per 24 hours.</p>
            </div>
        </div>
        <div class="answers-item" id="q6">
            <div class="answers-item__question">Which operating systems does {APP_NAME} work on?</div>
            <div class="answers-item__answer">
                <p>{APP_NAME} works on most popular desktop and mobile operating systems: Windows, OS X, Linux, Android, iOS. A protected Web panel is also available so you can manage your private cloud conveniently via your browser.</p>
            </div>
        </div>
        <div class="answers-item" id="q7">
            <div class="answers-item__question">What do I need to start working with {APP_NAME}?</div>
            <div class="answers-item__answer">
                <p>To get started, you need to register, then download and install the mobile or desktop application. After that, {APP_NAME} does all the rest!</p>
                <p>Once you have the app installed, you can add files to the directory for syncing, then share them with your colleagues and friends. If you add more than one device to your account, your content will automatically be synced across your devices.</p>
            </div>
        </div>
        <div class="answers-item" id="q8">
            <div class="answers-item__question">Why can I see {APP_NAME} connecting to its own servers; signal server, for example? You said this was a peer-2-peer service without a public cloud.</div>
            <div class="answers-item__answer">
                <p>‘Peer-2-peer’ connection implies a certain signal server that ’connects’ users’ devices. Additionally, a user’s devices might be part of a local network with complex topology or be behind a firewall. In such cases, connection to these devices is only possible via a turn (proxy) server. But one thing stays the same: <span class="faq-bold">Your traffic is protected by end-2-end encryption and your content is saved only on your devices, and nowhere else.</span></p>
            </div>
        </div>
        <div class="answers-item" id="q9">
            <div class="answers-item__question">Who is {APP_NAME} useful for?</div>
            <div class="answers-item__answer">
                <p>For any individual or business who cares about the security and confidentiality of their data and is accustomed to being the sole and complete owner of their own information.</p>
                <p>{APP_NAME} is useful for anyone who wants to transfer data from A to B quickly and safely. It might be a couple dozen photos, or hundred-terabyte files located at opposite ends of the globe.</p>
                <p class="footnote">*There are limits on transfer for the <span class="faq-bold">‘Free’</span> tariff: 1GB maximum file size and no more than 3 links per 24 hours.</p>
            </div>
        </div>
        <div class="answers-item" id="q10">
            <div class="answers-item__question">I’ve found my copyrighted content on public links. How can I delete it?</div>
            <div class="answers-item__answer">
                <p>{APP_NAME} allows information to be transferred between users via automatic syncing of devices, collaboration folders, or public https:// links. In the case of the former two, we will have no knowledge of what information gets transferred. However, if information is passed on via https:// links to be accessed by an unlimited number of viewers and is unprotected; by password, for example, then it will become public. If we receive a validated complaint about such a link, we can deactivate it at the service level.</p>
                <p>Please note that we will ask you for all documents that confirm your ownership of intellectual property.<br></p>Queries can be sent to: <a href="mailto:abuse@' . Yii::getAlias('@frontendDomain') . '">abuse@' . Yii::getAlias('@frontendDomain') . '</a>
            </div>
        </div>
        <div class="answers-item" id="q11">
            <div class="answers-item__question">How do you process data requests?</div>
            <div class="answers-item__answer">
                <p>Thanks to the end-2-end encryption imbedded in our apps, no one can intercept or decrypt your data. Therefore, in terms of technology, it is impossible for files that belong to you to be transferred to anybody else. <br>In all other cases, we need a court order to disclose any portion of the small amount of information we own.</p>
            </div>
        </div>
    </div>
    <div class="answers-section">
        <div class="answers-chapter" id="ch2">Desktop & mobile application questions</div>
        <div class="answers-item" id="q12">
            <div class="answers-item__question">The app won’t run from my smartphone and/or computer. What can I do?</div>
            <div class="answers-item__answer">
                <p>Make sure your computer or smartphone has the required minimum version of a compatible operating system. For Windows - Windows 7 or later, Mac - OS X 10.11 or later, Android - 5.0 or later, IOS - 10.0 or later. Correct operation on older versions cannot be guaranteed.</p>
                <p>Make sure your device has enough free space for the app to run and operate, and also that any defensive software (for instance, antivirus, firewalls etc.) will allow {APP_NAME} to run.</p>
                <p>Get in touch with us if you need any help and we will do our best to solve your problem.</p>
            </div>
        </div>
        <div class="answers-item" id="q13">
            <div class="answers-item__question">I’ve installed the app. How can I transfer files to my other devices or to friends/colleagues?</div>
            <div class="answers-item__answer">
                <p>Once you have the app installed, there are three modes for transferring files to other devices:</p>
                <ul>
                    <li>Automatic file syncing across your personal devices. This is the default setting*.</li>
                    <li>Transfer via a shared collaboration folder. A shared folder allows users to work jointly on documents, as well as to add files. To do this, you need to choose a folder and invite a friend to join it*.</li>
                    <li>Transfer via https:// link. Anyone can download the file from a browser.</li>
                </ul>
                <p class="footnote">*Not available with the <span class="faq-bold">‘Free’</span> tariff.</p>
            </div>
        </div>
        <div class="answers-item" id="q14">
            <div class="answers-item__question">What’s a ‘collaboration folder’? How does it work?</div>
            <div class="answers-item__answer">
                <p>A collaboration folder is a folder which is commonly available to you and your friends/colleagues. Any content uploaded to this folder by any of its collaborators will also be displayed to all other participants to the folder.</p>
                <p>To invite a friend or colleague for collaboration, choose a folder via the Web panel or via the mobile app, right click on it, then <span class="faq-italic">‘Share → Collaborations settings → Add colleague’</span>. Enter your colleague’s email address in this field, then click ’Invite’. An email with an invitation will then be sent to the address given. Once your friend/colleague clicks on the link in the email, they will become a collaborator and have access to the collaboration folder.</p>
                <p>Of course, they will also need to have the {APP_NAME} app installed.</p>
            </div>
        </div>
        <div class="answers-item" id="q15">
            <div class="answers-item__question">Can I set up a password for downloading files from an https:// link, or a self-destruct timer on the link itself?</div>
            <div class="answers-item__answer">
                <p>Yes. These settings are available when you create a link via the mobile app or Web panel.</p>
                <p class="footnote">*Not available with the <span class="faq-bold">‘Free’</span> tariff.</p>
            </div>
        </div>
        <div class="answers-item" id="q16">
            <div class="answers-item__question">What’s the difference between ‘backup’ and ‘backup-less’ mode on the desktop application?</div>
            <div class="answers-item__answer">
                <p>On running the desktop app for the first time, the system offers you to choose a certain setting:
                ‘Let the system make backups of your files’. If you allow the system to make a reserve copy of your files,
                you will be able to restore any file for up to 30 days after its deletion. Naturally,
                this setting takes up space on your hard drive.</p>
                <p>If you operate the system in backup-less mode, it is not possible to restore deleted files.
                The system will only work for transferring files.</p>
            </div>
        </div>
        <div class="answers-item" id="q17">
            <div class="answers-item__question">Does {APP_NAME} have version history support for files?</div>
            <div class="answers-item__answer">
                <p>Yes. You can restore any version of a particular file from the past 30 days.
                This is useful if, for example, you have deleted a file accidentally and want to get it back,
                or if a file\'s contents have changed and you want to restore a previous version.</p>
            </div>
        </div>
        <div class="answers-item" id="q18">
            <div class="answers-item__question">How can I restore a previous version of a file?</div>
            <div class="answers-item__answer">
                <p>You can set this up via the Web panel. Go onto the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a>,
                enter your account and right click the relevant file, then choose <span class="faq-italic">‘File versions’</span>.</p>
            </div>
        </div>
        <div class="answers-item" id="q19">
            <div class="answers-item__question">How can I save disk space on my computer while using {APP_NAME}?</div>
            <div class="answers-item__answer">
                <p>When your computer syncs to your other devices, it downloads all available files in
                collaboration folders on other devices. It might turn out that there is not enough space on your hard drive.
                You can solve this problem by using the function <span class="faq-italic">‘Selective sync’</span>.
                Go into <span class="faq-italic">‘Settings → Selective sync’</span>, then remove the tick from the folder you don’t want to sync with your computer.</p>
            </div>
        </div>
        <div class="answers-item" id="q20">
            <div class="answers-item__question">There isn’t a lot of space on my smartphone. How can I receive files from my computer and at the same time save disk space?</div>
            <div class="answers-item__answer">
                <p>As a default, all files received by your mobile device in sync mode will be displayed as ‘placeholders’.
                ‘Placeholders’ take up practically no space, but if you ‘tap’ a placeholder, the file will automatically
                be downloaded from a device available online. Additionally, in ‘Settings’ there is the option
                <span class="faq-italic">‘Automatically download media files &lt; 10MB’</span>. Turning this off will help save space on your mobile.</p>
            </div>
        </div>
        <div class="answers-item" id="q21">
            <div class="answers-item__question">I want a certain file/folder on my smartphone to always be the currentversion, and for it to always be available (downloaded from other devices). How can this be done?</div>
            <div class="answers-item__answer">
                <p>If you activate the setting <span class="faq-italic">‘Create offline copy’</span> for a file or folder,
                then backups will be created for it and your smartphone will automatically look for new versions.
                You’ll always have the latest version of the file on your phone.</p>
            </div>
        </div>
        <div class="answers-item" id="q22">
            <div class="answers-item__question">How can I transfer a photo & video gallery from a smartphone to a computer?</div>
            <div class="answers-item__answer">
                <p>In the mobile app’s settings choose the option ‘Automatically upload photos and videos from camera’
                and simultaneously run {APP_NAME} on your computer.
                The gallery from your smartphone will be synced to your computer.</p>
            </div>
        </div>
        <div class="answers-item" id="q23">
            <div class="answers-item__question">I no longer have access to a device which {APP_NAME} is installed on. Can I wipe information from the sync folder remotely?</div>
            <div class="answers-item__answer">
                <p>Yes. You can wipe information remotely and log out of the account on that device.
                To do this, log into your account on the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a>, then go to the option
                <span class="faq-italic">‘My devices → Manage</span>, and select <span class="faq-italic">‘Log out & Remote wipe’</span>.
                The data will disappear as soon as the device appears online.</p>
                <p><span class="faq-small faq-gray">*Not available with the <span class="faq-bold">‘Free’</span> tariff.</span></p>
            </div>
        </div>
        <div class="answers-item" id="q24">
            <div class="answers-item__question">How can I view IP addresses and devices connected to my account?</div>
            <div class="answers-item__answer">
                <p>Log into your account on the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a>, then go to the option
                <span class="faq-italic">‘My devices → Manage’</span>, and you will see a list of IP addresses and IDs of devices
                that {APP_NAME} has been run from.</p>
            </div>
        </div>
        <div class="answers-item" id="q25">
            <div class="answers-item__question">Can I delete my {APP_NAME} account?</div>
            <div class="answers-item__answer">
                <p>Yes. You can do this via <span class="faq-italic">‘Web panel → Settings’</span>. If you delete your account,
                you will automatically be logged out on all your devices and your emails and file metadata
                will be permanently and irretrievably deleted from our servers.
                Files stored locally on your devices in the {APP_NAME} sync folder will not be deleted.</p>
                <p>Should you delete your account, it will not be possible to restore it.</p>
            </div>
        </div>
    </div>
    <div class="answers-section">
        <div class="answers-chapter" id="ch3">Business user questions</div>
        <div class="answers-item" id="q26">
            <div class="answers-item__question">How does the <span class="faq-bold">‘Business’</span> tariff differ from the <span class="faq-bold">‘Professional’</span> tariff?</div>
            <div class="answers-item__answer">
                <p>The <span class="faq-bold">‘Business’</span> tariff is designed for the specific needs of businesses and has additional
                functions as compared to the <span class="faq-bold">‘Professional’</span> tariff.</p>
                <p>In the <span class="faq-bold">‘Business’</span> tariff, there is an <span class="faq-italic">‘Admin panel’</span> for business administrators.
                Via this panel, you can add co-workers, grant them the right to access collaboration folders,
                and also monitor a log of actions carried out by your co-workers; what they have worked on and when.
                This allows you in turn to additionally control the working process.</p>
            </div>
        </div>
        <div class="answers-item" id="q27">
            <div class="answers-item__question">I’ve just got a new co-worker. How can I invite him/her for joint work on our documents?</div>
            <div class="answers-item__answer">
                <p>As a business administrator, go to <span class="faq-italic">‘Admin panel → Collaboration settings → Add license’</span>.
                You will be asked to choose the number of required additional licenses and can them to those already existing.
                Once you have done this, you can invite the new co-worker.</p>
            </div>
        </div>
        <div class="answers-item" id="q28">
            <div class="answers-item__question">I have a ‘Business Administrator’ account. How can I monitor my co-workers’ actions log?</div>
            <div class="answers-item__answer">
                <p>As a business administrator, if you go to the tab <span class="faq-italic">‘Admin panel → Reports’</span>,
                you will be able to view the actions log for every co-worker in your joint collaboration folder.</p>
            </div>
        </div>
        <div class="answers-item" id="q29">
            <div class="answers-item__question">I need 100% (or thereabouts) uptime for our collaboration folder. How can this be done?</div>
            <div class="answers-item__answer">
                <p>You can always set up {APP_NAME} on a personal or hired server with a high level of uptime.
                If you do this, you and your co-workers’ collaboration folder will always be online,
                and as a result any changes made by you or your co-workers will be
                instantaneously uploaded to your collaboration folder.</p>
            </div>
        </div>
        <div class="answers-item" id="q30">
            <div class="answers-item__question">I have a problem with using {APP_NAME}. Who can I get in touch with?</div>
            <div class="answers-item__answer">
                <p>You can leave a question or describe your problem in the <a href="' . Url::to(['/support'], CREATE_ABSOLUTE_URL) . '">form</a>,
                or send us an email to <a href="mailto:{supportEmail_TECHNICAL}">{supportEmail_TECHNICAL}</a></p>
                <p>We will try to get back to you as quickly as possible.</p>
            </div>
        </div>
    </div>
</div>
',
];