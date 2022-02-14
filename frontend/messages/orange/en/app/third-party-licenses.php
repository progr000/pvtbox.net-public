<?php
return [
    'title' => 'Third Party Licenses',
    'htm_text' => '
<div class="md wiki">
<h2 data-sourcepos="13:1-13:58" dir="auto">
<a id="user-content-third-party-licenses-used-in-pvtbox-desktop-application" class="anchor" href="#third-party-licenses-used-in-pvtbox-desktop-application" aria-hidden="true"></a>Third-party licenses used in Pvtbox desktop application</h2>
<p data-sourcepos="15:1-15:29" dir="auto"><em>Last updated: June 20, 2019</em></p>
<p data-sourcepos="17:1-19:71" dir="auto">Pvtbox desktop application includes a number of third-party libraries that are used to provide certain features.<br>
All those libraries distributing with Pvtbox in desktop application embedded or alongside.<br>
Some of those libraries require additional information described below.</p>
<h3 data-sourcepos="21:1-21:6" dir="auto">
<a id="user-content-qt" class="anchor" href="#qt" aria-hidden="true"></a>Qt</h3>
<p data-sourcepos="23:1-24:81" dir="auto">Pvtbox desktop application uses Qt library version 5.12 licensed under LGPLv3 which is available on <a href="https://doc.qt.io/qt-5/lgpl.html" rel="nofollow noreferrer noopener" target="_blank">Qt website</a> and in this document in Licenses section.<br>
Qt uses some third-party libraries, their licenses described in Licenses section.</p>
<p data-sourcepos="26:1-26:183" dir="auto">Pvtbox desktop application distributing with Qt binaries on Mac OS X, Windows and Linux platforms except openSUSE. Pvtbox uses system installation of Qt on openSUSE Linux platforms.</p>
<p data-sourcepos="28:1-28:242" dir="auto">Qt sources used to build binaries distributed with Pvtbox desktop application available on <a href="http://code.qt.io/cgit/" rel="nofollow noreferrer noopener" target="_blank">Qt code repository</a> and <a href="http://installer.pvtbox.net/third-party/qt-everywhere-src-5.12.3.tar.xz" rel="nofollow noreferrer noopener" target="_blank">as archive on out website</a>.</p>
<p data-sourcepos="30:1-31:38" dir="auto">You can build qt sources to get binaries with instruction on <a href="http://doc.qt.io/qt-5" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.<br>
Or you can get already built binaries.</p>
<p data-sourcepos="33:1-33:12" dir="auto">On Mac OS X:</p>
<ul data-sourcepos="34:1-37:0" dir="auto">
<li data-sourcepos="34:1-34:40">via macports: <code>sudo port install qt5</code>;</li>
<li data-sourcepos="35:1-35:35">via homebrew: <code>brew install qt5</code>;</li>
<li data-sourcepos="36:1-37:0">download on <a href="https://download.qt.io/official_releases/qt/" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.</li>
</ul>
<p data-sourcepos="38:1-38:11" dir="auto">On Windows:</p>
<ul data-sourcepos="39:1-40:0" dir="auto">
<li data-sourcepos="39:1-40:0">download on <a href="https://download.qt.io/official_releases/qt/" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.</li>
</ul>
<p data-sourcepos="41:1-41:9" dir="auto">On Linux:</p>
<ul data-sourcepos="42:1-45:0" dir="auto">
<li data-sourcepos="42:1-42:32">via apt: <code>apt install libqt5</code>;</li>
<li data-sourcepos="43:1-43:29">via yum: <code>yum install qt5</code>;</li>
<li data-sourcepos="44:1-45:0">download on <a href="https://download.qt.io/official_releases/qt/" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.</li>
</ul>
<p data-sourcepos="46:1-46:115" dir="auto">To change Qt binaries distributed with PrivateBox desktop application with your own ones, you need to do following:</p>
<p data-sourcepos="48:1-48:12" dir="auto">On Mac OS X:</p>
<ol data-sourcepos="49:1-50:0" dir="auto">
<li data-sourcepos="49:1-50:0">replace <code>QtCore</code>, <code>QtDBus</code>, <code>QtGui</code>, <code>QtNetwork</code>, <code>QtPrintSupport</code>, <code>QtQml</code>, <code>QtQuick</code>, <code>QtSvg</code>, <code>QtWebSockets</code>, <code>QtWidgets</code> binaries and <code>qt5_plugins</code> directory inside <code>/Applications/Pvtbox.app/Contents/Frameworks/</code> with your own;</li>
</ol>
<p data-sourcepos="51:1-51:11" dir="auto">On Windows:</p>
<ol data-sourcepos="52:1-53:0" dir="auto">
<li data-sourcepos="52:1-53:0">replace <code>Qt5Core.dll</code>, <code>Qt5Gui.dll</code>, <code>Qt5Network.dll</code>, <code>Qt5Qml.dll</code>, <code>Qt5Quick.dll</code>, <code>Qt5Svg.dll</code>, <code>Qt5WebSockets.dll</code>, <code>Qt5Widgets.dll</code> binaries and <code>qt5_plugins</code> directory inside <code>C:\Program Files\Pvtbox\</code> with your own.</li>
</ol>
<p data-sourcepos="54:1-54:9" dir="auto">On Linux:</p>
<ol data-sourcepos="55:1-56:0" dir="auto">
<li data-sourcepos="55:1-56:0">replace <code>libQt5Core.so.5</code>, <code>libQt5DBus.so.5</code>, <code>libQt5EglFSDeviceIntegration.so.5</code>, <code>libQt5Gui.so.5</code>, <code>libQt5Network.so.5</code>, <code>libQt5Qml.so.5</code>, <code>libQt5Quick.so.5</code>, <code>libQt5Svg.so.5</code>, <code>libQt5WaylandClient.so.5</code>, <code>libQt5WebSockets.so.5</code>, <code>libQt5Widgets.so.5</code>, <code>libQt5XcbQpa.so.5</code> binaries and <code>qt5_plugins</code> directory inside <code>/opt/pvtbox/</code> with your own.</li>
</ol>
<p data-sourcepos="57:1-57:18" dir="auto">On openSUSE linux:</p>
<ol data-sourcepos="58:1-59:0" dir="auto">
<li data-sourcepos="58:1-59:0">change system installed Qt packages with your own.</li>
</ol>
<p data-sourcepos="60:1-60:112" dir="auto">After changing Qt binaries to your own, you can run Pvtbox desktop application and it will use your Qt binaries.</p>
<h3 data-sourcepos="62:1-62:11" dir="auto">
<a id="user-content-pyside2" class="anchor" href="#pyside2" aria-hidden="true"></a>PySide2</h3>
<p data-sourcepos="64:1-65:86" dir="auto">Pvtbox desktop application uses PySide2 version 5.12 licensed under LGPLv3 which is available on <a href="https://code.qt.io/cgit/pyside/pyside-setup.git/tree/LICENSE.LGPLv3" rel="nofollow noreferrer noopener" target="_blank">PySide2 code website</a> and in this document in Licenses section.<br>
PySide2 uses some third-party libraries, their licenses described in Licenses section.</p>
<p data-sourcepos="68:1-68:189" dir="auto">Pvtbox desktop application distributing with PySide2 binaries on Mac OS X, Windows and Linux platforms except openSUSE. Pvtbox uses PySide2 system installation on openSUSE Linux platforms.</p>
<p data-sourcepos="70:1-70:287" dir="auto">PySide2 sources used to build binaries distributed with Pvtbox desktop application available on <a href="https://code.qt.io/cgit/pyside/pyside-setup.git/" rel="nofollow noreferrer noopener" target="_blank">PySide2 code repository</a> and <a href="http://installer.pvtbox.net/third-party/pyside-setup-everywhere-src-5.12.3.tar.xz" rel="nofollow noreferrer noopener" target="_blank">as archive on out website</a>.</p>
<p data-sourcepos="72:1-73:38" dir="auto">You can build PySide sources to get binaries with instruction on <a href="https://wiki.qt.io/Qt_for_Python/GettingStarted" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.<br>
Or you can get already built binaries.</p>
<p data-sourcepos="75:1-75:12" dir="auto">On Mac OS X:</p>
<ul data-sourcepos="76:1-78:0" dir="auto">
<li data-sourcepos="76:1-76:32">via pip: <code>pip install PySide2</code>
</li>
<li data-sourcepos="77:1-78:0">download on <a href="https://download.qt.io/official_releases/QtForPython/pyside2/" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.</li>
</ul>
<p data-sourcepos="79:1-79:11" dir="auto">On Windows:</p>
<ul data-sourcepos="80:1-82:0" dir="auto">
<li data-sourcepos="80:1-80:33">via pip: <code>pip install PySide2</code>;</li>
<li data-sourcepos="81:1-82:0">download on <a href="https://download.qt.io/official_releases/QtForPython/pyside2/" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.</li>
</ul>
<p data-sourcepos="83:1-83:9" dir="auto">On Linux:</p>
<ul data-sourcepos="84:1-86:0" dir="auto">
<li data-sourcepos="84:1-84:33">via pip: <code>pip install PySide2</code>;</li>
<li data-sourcepos="85:1-86:0">download on <a href="https://download.qt.io/official_releases/QtForPython/pyside2/" rel="nofollow noreferrer noopener" target="_blank">Qt website</a>.</li>
</ul>
<p data-sourcepos="87:1-87:116" dir="auto">To change PySide2 binaries distributed with Pvtbox desktop application with your own ones, you need to do following:</p>
<p data-sourcepos="89:1-89:12" dir="auto">On Mac OS X:</p>
<ol data-sourcepos="90:1-91:0" dir="auto">
<li data-sourcepos="90:1-91:0">replace binaries in <code>PySide2</code> directory inside <code>/Applications/Pvtbox.app/Contents/Frameworks/</code> with your own.</li>
</ol>
<p data-sourcepos="92:1-92:11" dir="auto">On Windows:</p>
<ol data-sourcepos="93:1-94:0" dir="auto">
<li data-sourcepos="93:1-94:0">replace binaries in <code>PySide2</code> directory inside <code>C:\Program Files\Pvtbox\</code> with your own.</li>
</ol>
<p data-sourcepos="95:1-95:9" dir="auto">On Linux:</p>
<ol data-sourcepos="96:1-97:0" dir="auto">
<li data-sourcepos="96:1-97:0">replace binaries in <code>PySide2</code> directory inside <code>/opt/pvtbox/</code> with your own.</li>
</ol>
<p data-sourcepos="98:1-98:18" dir="auto">On openSUSE Linux:</p>
<ol data-sourcepos="99:1-100:0" dir="auto">
<li data-sourcepos="99:1-100:0">Change system installed PySide2 packages with your own.</li>
</ol>
<p data-sourcepos="101:1-101:126" dir="auto">After changing PySide2 binaries to your own, you can run PrivateBox desktop application and it will use your PySide2 binaries.</p>
<h2 data-sourcepos="103:1-103:11" dir="auto">
<a id="user-content-licenses" class="anchor" href="#licenses" aria-hidden="true"></a>Licenses</h2>
<h3 data-sourcepos="105:1-105:37" dir="auto">
<a id="user-content-gnu-lesser-general-public-license" class="anchor" href="#gnu-lesser-general-public-license" aria-hidden="true"></a>GNU LESSER GENERAL PUBLIC LICENSE</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">            GNU LESSER GENERAL PUBLIC LICENSE</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext"> The Qt Toolkit is Copyright (C) 2015 The Qt Company Ltd.</span>
<span id="LC5" class="line" lang="plaintext"> Contact: http://www.qt.io/licensing/</span>
<span id="LC6" class="line" lang="plaintext"></span>
<span id="LC7" class="line" lang="plaintext"> You may use, distribute and copy the Qt Toolkit under the terms of</span>
<span id="LC8" class="line" lang="plaintext"> GNU Lesser General Public License version 3, which is displayed below.</span>
<span id="LC9" class="line" lang="plaintext"> This license makes reference to the version 3 of the GNU General</span>
<span id="LC10" class="line" lang="plaintext"> Public License, which you can find in the LICENSE.GPLv3 file.</span>
<span id="LC11" class="line" lang="plaintext"></span>
<span id="LC12" class="line" lang="plaintext">-------------------------------------------------------------------------</span>
<span id="LC13" class="line" lang="plaintext"></span>
<span id="LC14" class="line" lang="plaintext">                   GNU LESSER GENERAL PUBLIC LICENSE</span>
<span id="LC15" class="line" lang="plaintext">                       Version 3, 29 June 2007</span>
<span id="LC16" class="line" lang="plaintext"></span>
<span id="LC17" class="line" lang="plaintext"> Copyright (C) 2007 Free Software Foundation, Inc. &lt;http://fsf.org/&gt;</span>
<span id="LC18" class="line" lang="plaintext"> Everyone is permitted to copy and distribute verbatim copies</span>
<span id="LC19" class="line" lang="plaintext"> of this license document, but changing it is not allowed.</span>
<span id="LC20" class="line" lang="plaintext"></span>
<span id="LC21" class="line" lang="plaintext"></span>
<span id="LC22" class="line" lang="plaintext">  This version of the GNU Lesser General Public License incorporates</span>
<span id="LC23" class="line" lang="plaintext">the terms and conditions of version 3 of the GNU General Public</span>
<span id="LC24" class="line" lang="plaintext">License, supplemented by the additional permissions listed below.</span>
<span id="LC25" class="line" lang="plaintext"></span>
<span id="LC26" class="line" lang="plaintext">  0. Additional Definitions.</span>
<span id="LC27" class="line" lang="plaintext"></span>
<span id="LC28" class="line" lang="plaintext">  As used herein, "this License" refers to version 3 of the GNU Lesser</span>
<span id="LC29" class="line" lang="plaintext">General Public License, and the "GNU GPL" refers to version 3 of the GNU</span>
<span id="LC30" class="line" lang="plaintext">General Public License.</span>
<span id="LC31" class="line" lang="plaintext"></span>
<span id="LC32" class="line" lang="plaintext">  "The Library" refers to a covered work governed by this License,</span>
<span id="LC33" class="line" lang="plaintext">other than an Application or a Combined Work as defined below.</span>
<span id="LC34" class="line" lang="plaintext"></span>
<span id="LC35" class="line" lang="plaintext">  An "Application" is any work that makes use of an interface provided</span>
<span id="LC36" class="line" lang="plaintext">by the Library, but which is not otherwise based on the Library.</span>
<span id="LC37" class="line" lang="plaintext">Defining a subclass of a class defined by the Library is deemed a mode</span>
<span id="LC38" class="line" lang="plaintext">of using an interface provided by the Library.</span>
<span id="LC39" class="line" lang="plaintext"></span>
<span id="LC40" class="line" lang="plaintext">  A "Combined Work" is a work produced by combining or linking an</span>
<span id="LC41" class="line" lang="plaintext">Application with the Library.  The particular version of the Library</span>
<span id="LC42" class="line" lang="plaintext">with which the Combined Work was made is also called the "Linked</span>
<span id="LC43" class="line" lang="plaintext">Version".</span>
<span id="LC44" class="line" lang="plaintext"></span>
<span id="LC45" class="line" lang="plaintext">  The "Minimal Corresponding Source" for a Combined Work means the</span>
<span id="LC46" class="line" lang="plaintext">Corresponding Source for the Combined Work, excluding any source code</span>
<span id="LC47" class="line" lang="plaintext">for portions of the Combined Work that, considered in isolation, are</span>
<span id="LC48" class="line" lang="plaintext">based on the Application, and not on the Linked Version.</span>
<span id="LC49" class="line" lang="plaintext"></span>
<span id="LC50" class="line" lang="plaintext">  The "Corresponding Application Code" for a Combined Work means the</span>
<span id="LC51" class="line" lang="plaintext">object code and/or source code for the Application, including any data</span>
<span id="LC52" class="line" lang="plaintext">and utility programs needed for reproducing the Combined Work from the</span>
<span id="LC53" class="line" lang="plaintext">Application, but excluding the System Libraries of the Combined Work.</span>
<span id="LC54" class="line" lang="plaintext"></span>
<span id="LC55" class="line" lang="plaintext">  1. Exception to Section 3 of the GNU GPL.</span>
<span id="LC56" class="line" lang="plaintext"></span>
<span id="LC57" class="line" lang="plaintext">  You may convey a covered work under sections 3 and 4 of this License</span>
<span id="LC58" class="line" lang="plaintext">without being bound by section 3 of the GNU GPL.</span>
<span id="LC59" class="line" lang="plaintext"></span>
<span id="LC60" class="line" lang="plaintext">  2. Conveying Modified Versions.</span>
<span id="LC61" class="line" lang="plaintext"></span>
<span id="LC62" class="line" lang="plaintext">  If you modify a copy of the Library, and, in your modifications, a</span>
<span id="LC63" class="line" lang="plaintext">facility refers to a function or data to be supplied by an Application</span>
<span id="LC64" class="line" lang="plaintext">that uses the facility (other than as an argument passed when the</span>
<span id="LC65" class="line" lang="plaintext">facility is invoked), then you may convey a copy of the modified</span>
<span id="LC66" class="line" lang="plaintext">version:</span>
<span id="LC67" class="line" lang="plaintext"></span>
<span id="LC68" class="line" lang="plaintext">   a) under this License, provided that you make a good faith effort to</span>
<span id="LC69" class="line" lang="plaintext">   ensure that, in the event an Application does not supply the</span>
<span id="LC70" class="line" lang="plaintext">   function or data, the facility still operates, and performs</span>
<span id="LC71" class="line" lang="plaintext">   whatever part of its purpose remains meaningful, or</span>
<span id="LC72" class="line" lang="plaintext"></span>
<span id="LC73" class="line" lang="plaintext">   b) under the GNU GPL, with none of the additional permissions of</span>
<span id="LC74" class="line" lang="plaintext">   this License applicable to that copy.</span>
<span id="LC75" class="line" lang="plaintext"></span>
<span id="LC76" class="line" lang="plaintext">  3. Object Code Incorporating Material from Library Header Files.</span>
<span id="LC77" class="line" lang="plaintext"></span>
<span id="LC78" class="line" lang="plaintext">  The object code form of an Application may incorporate material from</span>
<span id="LC79" class="line" lang="plaintext">a header file that is part of the Library.  You may convey such object</span>
<span id="LC80" class="line" lang="plaintext">code under terms of your choice, provided that, if the incorporated</span>
<span id="LC81" class="line" lang="plaintext">material is not limited to numerical parameters, data structure</span>
<span id="LC82" class="line" lang="plaintext">layouts and accessors, or small macros, inline functions and templates</span>
<span id="LC83" class="line" lang="plaintext">(ten or fewer lines in length), you do both of the following:</span>
<span id="LC84" class="line" lang="plaintext"></span>
<span id="LC85" class="line" lang="plaintext">   a) Give prominent notice with each copy of the object code that the</span>
<span id="LC86" class="line" lang="plaintext">   Library is used in it and that the Library and its use are</span>
<span id="LC87" class="line" lang="plaintext">   covered by this License.</span>
<span id="LC88" class="line" lang="plaintext"></span>
<span id="LC89" class="line" lang="plaintext">   b) Accompany the object code with a copy of the GNU GPL and this license</span>
<span id="LC90" class="line" lang="plaintext">   document.</span>
<span id="LC91" class="line" lang="plaintext"></span>
<span id="LC92" class="line" lang="plaintext">  4. Combined Works.</span>
<span id="LC93" class="line" lang="plaintext"></span>
<span id="LC94" class="line" lang="plaintext">  You may convey a Combined Work under terms of your choice that,</span>
<span id="LC95" class="line" lang="plaintext">taken together, effectively do not restrict modification of the</span>
<span id="LC96" class="line" lang="plaintext">portions of the Library contained in the Combined Work and reverse</span>
<span id="LC97" class="line" lang="plaintext">engineering for debugging such modifications, if you also do each of</span>
<span id="LC98" class="line" lang="plaintext">the following:</span>
<span id="LC99" class="line" lang="plaintext"></span>
<span id="LC100" class="line" lang="plaintext">   a) Give prominent notice with each copy of the Combined Work that</span>
<span id="LC101" class="line" lang="plaintext">   the Library is used in it and that the Library and its use are</span>
<span id="LC102" class="line" lang="plaintext">   covered by this License.</span>
<span id="LC103" class="line" lang="plaintext"></span>
<span id="LC104" class="line" lang="plaintext">   b) Accompany the Combined Work with a copy of the GNU GPL and this license</span>
<span id="LC105" class="line" lang="plaintext">   document.</span>
<span id="LC106" class="line" lang="plaintext"></span>
<span id="LC107" class="line" lang="plaintext">   c) For a Combined Work that displays copyright notices during</span>
<span id="LC108" class="line" lang="plaintext">   execution, include the copyright notice for the Library among</span>
<span id="LC109" class="line" lang="plaintext">   these notices, as well as a reference directing the user to the</span>
<span id="LC110" class="line" lang="plaintext">   copies of the GNU GPL and this license document.</span>
<span id="LC111" class="line" lang="plaintext"></span>
<span id="LC112" class="line" lang="plaintext">   d) Do one of the following:</span>
<span id="LC113" class="line" lang="plaintext"></span>
<span id="LC114" class="line" lang="plaintext">       0) Convey the Minimal Corresponding Source under the terms of this</span>
<span id="LC115" class="line" lang="plaintext">       License, and the Corresponding Application Code in a form</span>
<span id="LC116" class="line" lang="plaintext">       suitable for, and under terms that permit, the user to</span>
<span id="LC117" class="line" lang="plaintext">       recombine or relink the Application with a modified version of</span>
<span id="LC118" class="line" lang="plaintext">       the Linked Version to produce a modified Combined Work, in the</span>
<span id="LC119" class="line" lang="plaintext">       manner specified by section 6 of the GNU GPL for conveying</span>
<span id="LC120" class="line" lang="plaintext">       Corresponding Source.</span>
<span id="LC121" class="line" lang="plaintext"></span>
<span id="LC122" class="line" lang="plaintext">       1) Use a suitable shared library mechanism for linking with the</span>
<span id="LC123" class="line" lang="plaintext">       Library.  A suitable mechanism is one that (a) uses at run time</span>
<span id="LC124" class="line" lang="plaintext">       a copy of the Library already present on the user\'s computer</span>
<span id="LC125" class="line" lang="plaintext">       system, and (b) will operate properly with a modified version</span>
<span id="LC126" class="line" lang="plaintext">       of the Library that is interface-compatible with the Linked</span>
<span id="LC127" class="line" lang="plaintext">       Version.</span>
<span id="LC128" class="line" lang="plaintext"></span>
<span id="LC129" class="line" lang="plaintext">   e) Provide Installation Information, but only if you would otherwise</span>
<span id="LC130" class="line" lang="plaintext">   be required to provide such information under section 6 of the</span>
<span id="LC131" class="line" lang="plaintext">   GNU GPL, and only to the extent that such information is</span>
<span id="LC132" class="line" lang="plaintext">   necessary to install and execute a modified version of the</span>
<span id="LC133" class="line" lang="plaintext">   Combined Work produced by recombining or relinking the</span>
<span id="LC134" class="line" lang="plaintext">   Application with a modified version of the Linked Version. (If</span>
<span id="LC135" class="line" lang="plaintext">   you use option 4d0, the Installation Information must accompany</span>
<span id="LC136" class="line" lang="plaintext">   the Minimal Corresponding Source and Corresponding Application</span>
<span id="LC137" class="line" lang="plaintext">   Code. If you use option 4d1, you must provide the Installation</span>
<span id="LC138" class="line" lang="plaintext">   Information in the manner specified by section 6 of the GNU GPL</span>
<span id="LC139" class="line" lang="plaintext">   for conveying Corresponding Source.)</span>
<span id="LC140" class="line" lang="plaintext"></span>
<span id="LC141" class="line" lang="plaintext">  5. Combined Libraries.</span>
<span id="LC142" class="line" lang="plaintext"></span>
<span id="LC143" class="line" lang="plaintext">  You may place library facilities that are a work based on the</span>
<span id="LC144" class="line" lang="plaintext">Library side by side in a single library together with other library</span>
<span id="LC145" class="line" lang="plaintext">facilities that are not Applications and are not covered by this</span>
<span id="LC146" class="line" lang="plaintext">License, and convey such a combined library under terms of your</span>
<span id="LC147" class="line" lang="plaintext">choice, if you do both of the following:</span>
<span id="LC148" class="line" lang="plaintext"></span>
<span id="LC149" class="line" lang="plaintext">   a) Accompany the combined library with a copy of the same work based</span>
<span id="LC150" class="line" lang="plaintext">   on the Library, uncombined with any other library facilities,</span>
<span id="LC151" class="line" lang="plaintext">   conveyed under the terms of this License.</span>
<span id="LC152" class="line" lang="plaintext"></span>
<span id="LC153" class="line" lang="plaintext">   b) Give prominent notice with the combined library that part of it</span>
<span id="LC154" class="line" lang="plaintext">   is a work based on the Library, and explaining where to find the</span>
<span id="LC155" class="line" lang="plaintext">   accompanying uncombined form of the same work.</span>
<span id="LC156" class="line" lang="plaintext"></span>
<span id="LC157" class="line" lang="plaintext">  6. Revised Versions of the GNU Lesser General Public License.</span>
<span id="LC158" class="line" lang="plaintext"></span>
<span id="LC159" class="line" lang="plaintext">  The Free Software Foundation may publish revised and/or new versions</span>
<span id="LC160" class="line" lang="plaintext">of the GNU Lesser General Public License from time to time. Such new</span>
<span id="LC161" class="line" lang="plaintext">versions will be similar in spirit to the present version, but may</span>
<span id="LC162" class="line" lang="plaintext">differ in detail to address new problems or concerns.</span>
<span id="LC163" class="line" lang="plaintext"></span>
<span id="LC164" class="line" lang="plaintext">  Each version is given a distinguishing version number. If the</span>
<span id="LC165" class="line" lang="plaintext">Library as you received it specifies that a certain numbered version</span>
<span id="LC166" class="line" lang="plaintext">of the GNU Lesser General Public License "or any later version"</span>
<span id="LC167" class="line" lang="plaintext">applies to it, you have the option of following the terms and</span>
<span id="LC168" class="line" lang="plaintext">conditions either of that published version or of any later version</span>
<span id="LC169" class="line" lang="plaintext">published by the Free Software Foundation. If the Library as you</span>
<span id="LC170" class="line" lang="plaintext">received it does not specify a version number of the GNU Lesser</span>
<span id="LC171" class="line" lang="plaintext">General Public License, you may choose any version of the GNU Lesser</span>
<span id="LC172" class="line" lang="plaintext">General Public License ever published by the Free Software Foundation.</span>
<span id="LC173" class="line" lang="plaintext"></span>
<span id="LC174" class="line" lang="plaintext">  If the Library as you received it specifies that a proxy can decide</span>
<span id="LC175" class="line" lang="plaintext">whether future versions of the GNU Lesser General Public License shall</span>
<span id="LC176" class="line" lang="plaintext">apply, that proxy\'s public statement of acceptance of any version is</span>
<span id="LC177" class="line" lang="plaintext">permanent authorization for you to choose that version for the</span>
<span id="LC178" class="line" lang="plaintext">Library.</span>
<span id="LC179" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="289:1-289:67" dir="auto">
<a id="user-content-the-independent-jpeg-groups-jpeg-software-libjpeg-version-8c" class="anchor" href="#the-independent-jpeg-groups-jpeg-software-libjpeg-version-8c" aria-hidden="true"></a>The Independent JPEG Group\'s JPEG Software (libjpeg) version 8c</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">In plain English:</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">1. We don\'t promise that this software works.  (But if you find any bugs,</span>
<span id="LC5" class="line" lang="plaintext">   please let us know!)</span>
<span id="LC6" class="line" lang="plaintext">2. You can use this software for whatever you want.  You don\'t have to pay us.</span>
<span id="LC7" class="line" lang="plaintext">3. You may not pretend that you wrote this software.  If you use it in a</span>
<span id="LC8" class="line" lang="plaintext">   program, you must acknowledge somewhere in your documentation that</span>
<span id="LC9" class="line" lang="plaintext">   you\'ve used the IJG code.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">In legalese:</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">The authors make NO WARRANTY or representation, either express or implied,</span>
<span id="LC14" class="line" lang="plaintext">with respect to this software, its quality, accuracy, merchantability, or</span>
<span id="LC15" class="line" lang="plaintext">fitness for a particular purpose.  This software is provided "AS IS", and you,</span>
<span id="LC16" class="line" lang="plaintext">its user, assume the entire risk as to its quality and accuracy.</span>
<span id="LC17" class="line" lang="plaintext"></span>
<span id="LC18" class="line" lang="plaintext">This software is copyright (C) 1991-2011, Thomas G. Lane, Guido Vollbeding.</span>
<span id="LC19" class="line" lang="plaintext">All Rights Reserved except as specified below.</span>
<span id="LC20" class="line" lang="plaintext"></span>
<span id="LC21" class="line" lang="plaintext">Permission is hereby granted to use, copy, modify, and distribute this</span>
<span id="LC22" class="line" lang="plaintext">software (or portions thereof) for any purpose, without fee, subject to these</span>
<span id="LC23" class="line" lang="plaintext">conditions:</span>
<span id="LC24" class="line" lang="plaintext">(1) If any part of the source code for this software is distributed, then this</span>
<span id="LC25" class="line" lang="plaintext">README file must be included, with this copyright and no-warranty notice</span>
<span id="LC26" class="line" lang="plaintext">unaltered; and any additions, deletions, or changes to the original files</span>
<span id="LC27" class="line" lang="plaintext">must be clearly indicated in accompanying documentation.</span>
<span id="LC28" class="line" lang="plaintext">(2) If only executable code is distributed, then the accompanying</span>
<span id="LC29" class="line" lang="plaintext">documentation must state that "this software is based in part on the work of</span>
<span id="LC30" class="line" lang="plaintext">the Independent JPEG Group".</span>
<span id="LC31" class="line" lang="plaintext">(3) Permission for use of this software is granted only if the user accepts</span>
<span id="LC32" class="line" lang="plaintext">full responsibility for any undesirable consequences; the authors accept</span>
<span id="LC33" class="line" lang="plaintext">NO LIABILITY for damages of any kind.</span>
<span id="LC34" class="line" lang="plaintext"></span>
<span id="LC35" class="line" lang="plaintext">These conditions apply to any software derived from or based on the IJG code,</span>
<span id="LC36" class="line" lang="plaintext">not just to the unmodified library.  If you use our work, you ought to</span>
<span id="LC37" class="line" lang="plaintext">acknowledge us.</span>
<span id="LC38" class="line" lang="plaintext"></span>
<span id="LC39" class="line" lang="plaintext">Permission is NOT granted for the use of any IJG author\'s name or company name</span>
<span id="LC40" class="line" lang="plaintext">in advertising or publicity relating to this software or products derived from</span>
<span id="LC41" class="line" lang="plaintext">it.  This software may be referred to only as "the Independent JPEG Group\'s</span>
<span id="LC42" class="line" lang="plaintext">software".</span>
<span id="LC43" class="line" lang="plaintext"></span>
<span id="LC44" class="line" lang="plaintext">We specifically permit and encourage the use of this software as the basis of</span>
<span id="LC45" class="line" lang="plaintext">commercial products, provided that all warranty or liability claims are</span>
<span id="LC46" class="line" lang="plaintext">assumed by the product vendor.</span>
<span id="LC47" class="line" lang="plaintext"></span>
<span id="LC48" class="line" lang="plaintext"></span>
<span id="LC49" class="line" lang="plaintext">ansi2knr.c is included in this distribution by permission of L. Peter Deutsch,</span>
<span id="LC50" class="line" lang="plaintext">sole proprietor of its copyright holder, Aladdin Enterprises of Menlo Park, CA.</span>
<span id="LC51" class="line" lang="plaintext">ansi2knr.c is NOT covered by the above copyright and conditions, but instead</span>
<span id="LC52" class="line" lang="plaintext">by the usual distribution terms of the Free Software Foundation; principally,</span>
<span id="LC53" class="line" lang="plaintext">that you must include source code if you redistribute it.  (See the file</span>
<span id="LC54" class="line" lang="plaintext">ansi2knr.c for full details.)  However, since ansi2knr.c is not needed as part</span>
<span id="LC55" class="line" lang="plaintext">of any program generated from the IJG code, this does not limit you more than</span>
<span id="LC56" class="line" lang="plaintext">the foregoing paragraphs do.</span>
<span id="LC57" class="line" lang="plaintext"></span>
<span id="LC58" class="line" lang="plaintext">The Unix configuration script "configure" was produced with GNU Autoconf.</span>
<span id="LC59" class="line" lang="plaintext">It is copyright by the Free Software Foundation but is freely distributable.</span>
<span id="LC60" class="line" lang="plaintext">The same holds for its supporting scripts (config.guess, config.sub,</span>
<span id="LC61" class="line" lang="plaintext">ltmain.sh).  Another support script, install-sh, is copyright by X Consortium</span>
<span id="LC62" class="line" lang="plaintext">but is also freely distributable.</span>
<span id="LC63" class="line" lang="plaintext"></span>
<span id="LC64" class="line" lang="plaintext">The IJG distribution formerly included code to read and write GIF files.</span>
<span id="LC65" class="line" lang="plaintext">To avoid entanglement with the Unisys LZW patent, GIF reading support has</span>
<span id="LC66" class="line" lang="plaintext">been removed altogether, and the GIF writer has been simplified to produce</span>
<span id="LC67" class="line" lang="plaintext">"uncompressed GIFs".  This technique does not use the LZW algorithm; the</span>
<span id="LC68" class="line" lang="plaintext">resulting GIF files are larger than usual, but are readable by all standard</span>
<span id="LC69" class="line" lang="plaintext">GIF decoders.</span>
<span id="LC70" class="line" lang="plaintext"></span>
<span id="LC71" class="line" lang="plaintext">We are required to state that</span>
<span id="LC72" class="line" lang="plaintext">    "The Graphics Interchange Format(c) is the Copyright property of</span>
<span id="LC73" class="line" lang="plaintext">    CompuServe Incorporated.  GIF(sm) is a Service Mark property of</span>
<span id="LC74" class="line" lang="plaintext">    CompuServe Incorporated."</span>
<span id="LC75" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="369:1-369:39" dir="auto">
<a id="user-content-mng-library-libmng-version-1010" class="anchor" href="#mng-library-libmng-version-1010" aria-hidden="true"></a>MNG Library (libmng) version 1.0.10</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">/* ************************************************************************** */</span>
<span id="LC3" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC4" class="line" lang="plaintext">/* * COPYRIGHT NOTICE:                                                      * */</span>
<span id="LC5" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC6" class="line" lang="plaintext">/* * Copyright (c) 2000-2007 Gerard Juyn (gerard@libmng.com)                     * */</span>
<span id="LC7" class="line" lang="plaintext">/* * [You may insert additional notices after this sentence if you modify   * */</span>
<span id="LC8" class="line" lang="plaintext">/* *  this source]                                                          * */</span>
<span id="LC9" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC10" class="line" lang="plaintext">/* * For the purposes of this copyright and license, "Contributing Authors" * */</span>
<span id="LC11" class="line" lang="plaintext">/* * is defined as the following set of individuals:                        * */</span>
<span id="LC12" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC13" class="line" lang="plaintext">/* *    Gerard Juyn                                                         * */</span>
<span id="LC14" class="line" lang="plaintext">/* *    Glenn Randers-Pehrson                                               * */</span>
<span id="LC15" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC16" class="line" lang="plaintext">/* * The MNG Library is supplied "AS IS".  The Contributing Authors         * */</span>
<span id="LC17" class="line" lang="plaintext">/* * disclaim all warranties, expressed or implied, including, without      * */</span>
<span id="LC18" class="line" lang="plaintext">/* * limitation, the warranties of merchantability and of fitness for any   * */</span>
<span id="LC19" class="line" lang="plaintext">/* * purpose.  The Contributing Authors assume no liability for direct,     * */</span>
<span id="LC20" class="line" lang="plaintext">/* * indirect, incidental, special, exemplary, or consequential damages,    * */</span>
<span id="LC21" class="line" lang="plaintext">/* * which may result from the use of the MNG Library, even if advised of   * */</span>
<span id="LC22" class="line" lang="plaintext">/* * the possibility of such damage.                                        * */</span>
<span id="LC23" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC24" class="line" lang="plaintext">/* * Permission is hereby granted to use, copy, modify, and distribute this * */</span>
<span id="LC25" class="line" lang="plaintext">/* * source code, or portions hereof, for any purpose, without fee, subject * */</span>
<span id="LC26" class="line" lang="plaintext">/* * to the following restrictions:                                         * */</span>
<span id="LC27" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC28" class="line" lang="plaintext">/* * 1. The origin of this source code must not be misrepresented;          * */</span>
<span id="LC29" class="line" lang="plaintext">/* *    you must not claim that you wrote the original software.            * */</span>
<span id="LC30" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC31" class="line" lang="plaintext">/* * 2. Altered versions must be plainly marked as such and must not be     * */</span>
<span id="LC32" class="line" lang="plaintext">/* *    misrepresented as being the original source.                        * */</span>
<span id="LC33" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC34" class="line" lang="plaintext">/* * 3. This Copyright notice may not be removed or altered from any source * */</span>
<span id="LC35" class="line" lang="plaintext">/* *    or altered source distribution.                                     * */</span>
<span id="LC36" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC37" class="line" lang="plaintext">/* * The Contributing Authors specifically permit, without fee, and         * */</span>
<span id="LC38" class="line" lang="plaintext">/* * encourage the use of this source code as a component to supporting     * */</span>
<span id="LC39" class="line" lang="plaintext">/* * the MNG and JNG file format in commercial products.  If you use this   * */</span>
<span id="LC40" class="line" lang="plaintext">/* * source code in a product, acknowledgment would be highly appreciated.  * */</span>
<span id="LC41" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC42" class="line" lang="plaintext">/* ************************************************************************** */</span>
<span id="LC43" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC44" class="line" lang="plaintext">/* * Parts of this software have been adapted from the libpng package.      * */</span>
<span id="LC45" class="line" lang="plaintext">/* * Although this library supports all features from the PNG specification * */</span>
<span id="LC46" class="line" lang="plaintext">/* * (as MNG descends from it) it does not require the libpng package.      * */</span>
<span id="LC47" class="line" lang="plaintext">/* * It does require the zlib library and optionally the IJG jpeg library,  * */</span>
<span id="LC48" class="line" lang="plaintext">/* * and/or the "little-cms" library by Marti Maria (depending on the       * */</span>
<span id="LC49" class="line" lang="plaintext">/* * inclusion of support for JNG and Full-Color-Management respectively.   * */</span>
<span id="LC50" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC51" class="line" lang="plaintext">/* * This library\'s function is primarily to read and display MNG           * */</span>
<span id="LC52" class="line" lang="plaintext">/* * animations. It is not meant as a full-featured image-editing           * */</span>
<span id="LC53" class="line" lang="plaintext">/* * component! It does however offer creation and editing functionality    * */</span>
<span id="LC54" class="line" lang="plaintext">/* * at the chunk level.                                                    * */</span>
<span id="LC55" class="line" lang="plaintext">/* * (future modifications may include some more support for creation       * */</span>
<span id="LC56" class="line" lang="plaintext">/* *  and or editing)                                                       * */</span>
<span id="LC57" class="line" lang="plaintext">/* *                                                                        * */</span>
<span id="LC58" class="line" lang="plaintext">/* ************************************************************************** */</span>
<span id="LC59" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="433:1-433:48" dir="auto">
<a id="user-content-png-reference-library-libpng-version-151" class="anchor" href="#png-reference-library-libpng-version-151" aria-hidden="true"></a>PNG Reference Library (libpng) version 1.5.1</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">This copy of the libpng notices is provided for your convenience.  In case of</span>
<span id="LC3" class="line" lang="plaintext">any discrepancy between this copy and the notices in the file png.h that is</span>
<span id="LC4" class="line" lang="plaintext">included in the libpng distribution, the latter shall prevail.</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">COPYRIGHT NOTICE, DISCLAIMER, and LICENSE:</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">If you modify libpng you may insert additional notices immediately following</span>
<span id="LC9" class="line" lang="plaintext">this sentence.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">This code is released under the libpng license.</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">libpng versions 1.2.6, August 15, 2004, through 1.6.17, March 26, 2015, are</span>
<span id="LC14" class="line" lang="plaintext">Copyright (c) 2004, 2006-2015 Glenn Randers-Pehrson, and are</span>
<span id="LC15" class="line" lang="plaintext">distributed according to the same disclaimer and license as libpng-1.2.5</span>
<span id="LC16" class="line" lang="plaintext">with the following individual added to the list of Contributing Authors</span>
<span id="LC17" class="line" lang="plaintext"></span>
<span id="LC18" class="line" lang="plaintext">   Cosmin Truta</span>
<span id="LC19" class="line" lang="plaintext"></span>
<span id="LC20" class="line" lang="plaintext">libpng versions 1.0.7, July 1, 2000, through 1.2.5 - October 3, 2002, are</span>
<span id="LC21" class="line" lang="plaintext">Copyright (c) 2000-2002 Glenn Randers-Pehrson, and are</span>
<span id="LC22" class="line" lang="plaintext">distributed according to the same disclaimer and license as libpng-1.0.6</span>
<span id="LC23" class="line" lang="plaintext">with the following individuals added to the list of Contributing Authors</span>
<span id="LC24" class="line" lang="plaintext"></span>
<span id="LC25" class="line" lang="plaintext">   Simon-Pierre Cadieux</span>
<span id="LC26" class="line" lang="plaintext">   Eric S. Raymond</span>
<span id="LC27" class="line" lang="plaintext">   Gilles Vollant</span>
<span id="LC28" class="line" lang="plaintext"></span>
<span id="LC29" class="line" lang="plaintext">and with the following additions to the disclaimer:</span>
<span id="LC30" class="line" lang="plaintext"></span>
<span id="LC31" class="line" lang="plaintext">   There is no warranty against interference with your enjoyment of the</span>
<span id="LC32" class="line" lang="plaintext">   library or against infringement.  There is no warranty that our</span>
<span id="LC33" class="line" lang="plaintext">   efforts or the library will fulfill any of your particular purposes</span>
<span id="LC34" class="line" lang="plaintext">   or needs.  This library is provided with all faults, and the entire</span>
<span id="LC35" class="line" lang="plaintext">   risk of satisfactory quality, performance, accuracy, and effort is with</span>
<span id="LC36" class="line" lang="plaintext">   the user.</span>
<span id="LC37" class="line" lang="plaintext"></span>
<span id="LC38" class="line" lang="plaintext">libpng versions 0.97, January 1998, through 1.0.6, March 20, 2000, are</span>
<span id="LC39" class="line" lang="plaintext">Copyright (c) 1998, 1999 Glenn Randers-Pehrson, and are</span>
<span id="LC40" class="line" lang="plaintext">distributed according to the same disclaimer and license as libpng-0.96,</span>
<span id="LC41" class="line" lang="plaintext">with the following individuals added to the list of Contributing Authors:</span>
<span id="LC42" class="line" lang="plaintext"></span>
<span id="LC43" class="line" lang="plaintext">   Tom Lane</span>
<span id="LC44" class="line" lang="plaintext">   Glenn Randers-Pehrson</span>
<span id="LC45" class="line" lang="plaintext">   Willem van Schaik</span>
<span id="LC46" class="line" lang="plaintext"></span>
<span id="LC47" class="line" lang="plaintext">libpng versions 0.89, June 1996, through 0.96, May 1997, are</span>
<span id="LC48" class="line" lang="plaintext">Copyright (c) 1996, 1997 Andreas Dilger</span>
<span id="LC49" class="line" lang="plaintext">Distributed according to the same disclaimer and license as libpng-0.88,</span>
<span id="LC50" class="line" lang="plaintext">with the following individuals added to the list of Contributing Authors:</span>
<span id="LC51" class="line" lang="plaintext"></span>
<span id="LC52" class="line" lang="plaintext">   John Bowler</span>
<span id="LC53" class="line" lang="plaintext">   Kevin Bracey</span>
<span id="LC54" class="line" lang="plaintext">   Sam Bushell</span>
<span id="LC55" class="line" lang="plaintext">   Magnus Holmgren</span>
<span id="LC56" class="line" lang="plaintext">   Greg Roelofs</span>
<span id="LC57" class="line" lang="plaintext">   Tom Tanner</span>
<span id="LC58" class="line" lang="plaintext"></span>
<span id="LC59" class="line" lang="plaintext">libpng versions 0.5, May 1995, through 0.88, January 1996, are</span>
<span id="LC60" class="line" lang="plaintext">Copyright (c) 1995, 1996 Guy Eric Schalnat, Group 42, Inc.</span>
<span id="LC61" class="line" lang="plaintext"></span>
<span id="LC62" class="line" lang="plaintext">For the purposes of this copyright and license, "Contributing Authors"</span>
<span id="LC63" class="line" lang="plaintext">is defined as the following set of individuals:</span>
<span id="LC64" class="line" lang="plaintext"></span>
<span id="LC65" class="line" lang="plaintext">   Andreas Dilger</span>
<span id="LC66" class="line" lang="plaintext">   Dave Martindale</span>
<span id="LC67" class="line" lang="plaintext">   Guy Eric Schalnat</span>
<span id="LC68" class="line" lang="plaintext">   Paul Schmidt</span>
<span id="LC69" class="line" lang="plaintext">   Tim Wegner</span>
<span id="LC70" class="line" lang="plaintext"></span>
<span id="LC71" class="line" lang="plaintext">The PNG Reference Library is supplied "AS IS".  The Contributing Authors</span>
<span id="LC72" class="line" lang="plaintext">and Group 42, Inc. disclaim all warranties, expressed or implied,</span>
<span id="LC73" class="line" lang="plaintext">including, without limitation, the warranties of merchantability and of</span>
<span id="LC74" class="line" lang="plaintext">fitness for any purpose.  The Contributing Authors and Group 42, Inc.</span>
<span id="LC75" class="line" lang="plaintext">assume no liability for direct, indirect, incidental, special, exemplary,</span>
<span id="LC76" class="line" lang="plaintext">or consequential damages, which may result from the use of the PNG</span>
<span id="LC77" class="line" lang="plaintext">Reference Library, even if advised of the possibility of such damage.</span>
<span id="LC78" class="line" lang="plaintext"></span>
<span id="LC79" class="line" lang="plaintext">Permission is hereby granted to use, copy, modify, and distribute this</span>
<span id="LC80" class="line" lang="plaintext">source code, or portions hereof, for any purpose, without fee, subject</span>
<span id="LC81" class="line" lang="plaintext">to the following restrictions:</span>
<span id="LC82" class="line" lang="plaintext"></span>
<span id="LC83" class="line" lang="plaintext">1. The origin of this source code must not be misrepresented.</span>
<span id="LC84" class="line" lang="plaintext"></span>
<span id="LC85" class="line" lang="plaintext">2. Altered versions must be plainly marked as such and must not</span>
<span id="LC86" class="line" lang="plaintext">   be misrepresented as being the original source.</span>
<span id="LC87" class="line" lang="plaintext"></span>
<span id="LC88" class="line" lang="plaintext">3. This Copyright notice may not be removed or altered from any</span>
<span id="LC89" class="line" lang="plaintext">   source or altered source distribution.</span>
<span id="LC90" class="line" lang="plaintext"></span>
<span id="LC91" class="line" lang="plaintext">The Contributing Authors and Group 42, Inc. specifically permit, without</span>
<span id="LC92" class="line" lang="plaintext">fee, and encourage the use of this source code as a component to</span>
<span id="LC93" class="line" lang="plaintext">supporting the PNG file format in commercial products.  If you use this</span>
<span id="LC94" class="line" lang="plaintext">source code in a product, acknowledgment is not required but would be</span>
<span id="LC95" class="line" lang="plaintext">appreciated.</span>
<span id="LC96" class="line" lang="plaintext"></span>
<span id="LC97" class="line" lang="plaintext"></span>
<span id="LC98" class="line" lang="plaintext">A "png_get_copyright" function is available, for convenient use in "about"</span>
<span id="LC99" class="line" lang="plaintext">boxes and the like:</span>
<span id="LC100" class="line" lang="plaintext"></span>
<span id="LC101" class="line" lang="plaintext">   printf("%s",png_get_copyright(NULL));</span>
<span id="LC102" class="line" lang="plaintext"></span>
<span id="LC103" class="line" lang="plaintext">Also, the PNG logo (in PNG format, of course) is supplied in the</span>
<span id="LC104" class="line" lang="plaintext">files "pngbar.png" and "pngbar.jpg (88x31) and "pngnow.png" (98x31).</span>
<span id="LC105" class="line" lang="plaintext"></span>
<span id="LC106" class="line" lang="plaintext">Libpng is OSI Certified Open Source Software.  OSI Certified Open Source is a</span>
<span id="LC107" class="line" lang="plaintext">certification mark of the Open Source Initiative.</span>
<span id="LC108" class="line" lang="plaintext"></span>
<span id="LC109" class="line" lang="plaintext">Glenn Randers-Pehrson</span>
<span id="LC110" class="line" lang="plaintext">glennrp at users.sourceforge.net</span>
<span id="LC111" class="line" lang="plaintext">March 26, 2015</span>
<span id="LC112" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="550:1-550:54" dir="auto">
<a id="user-content-tiff-software-distribution-libtiff-version-392" class="anchor" href="#tiff-software-distribution-libtiff-version-392" aria-hidden="true"></a>TIFF Software Distribution (libtiff) version 3.9.2</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright (c) 1988-1997 Sam Leffler</span>
<span id="LC3" class="line" lang="plaintext">Copyright (c) 1991-1997 Silicon Graphics, Inc.</span>
<span id="LC4" class="line" lang="plaintext"></span>
<span id="LC5" class="line" lang="plaintext">Permission to use, copy, modify, distribute, and sell this software and </span>
<span id="LC6" class="line" lang="plaintext">its documentation for any purpose is hereby granted without fee, provided</span>
<span id="LC7" class="line" lang="plaintext">that (i) the above copyright notices and this permission notice appear in</span>
<span id="LC8" class="line" lang="plaintext">all copies of the software and related documentation, and (ii) the names of</span>
<span id="LC9" class="line" lang="plaintext">Sam Leffler and Silicon Graphics may not be used in any advertising or</span>
<span id="LC10" class="line" lang="plaintext">publicity relating to the software without the specific, prior written</span>
<span id="LC11" class="line" lang="plaintext">permission of Sam Leffler and Silicon Graphics.</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS-IS" AND WITHOUT WARRANTY OF ANY KIND, </span>
<span id="LC14" class="line" lang="plaintext">EXPRESS, IMPLIED OR OTHERWISE, INCLUDING WITHOUT LIMITATION, ANY </span>
<span id="LC15" class="line" lang="plaintext">WARRANTY OF MERCHANTABILITY OR FITNESS FOR A PARTICULAR PURPOSE.  </span>
<span id="LC16" class="line" lang="plaintext"></span>
<span id="LC17" class="line" lang="plaintext">IN NO EVENT SHALL SAM LEFFLER OR SILICON GRAPHICS BE LIABLE FOR</span>
<span id="LC18" class="line" lang="plaintext">ANY SPECIAL, INCIDENTAL, INDIRECT OR CONSEQUENTIAL DAMAGES OF ANY KIND,</span>
<span id="LC19" class="line" lang="plaintext">OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS,</span>
<span id="LC20" class="line" lang="plaintext">WHETHER OR NOT ADVISED OF THE POSSIBILITY OF DAMAGE, AND ON ANY THEORY OF </span>
<span id="LC21" class="line" lang="plaintext">LIABILITY, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE </span>
<span id="LC22" class="line" lang="plaintext">OF THIS SOFTWARE.</span>
<span id="LC23" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="578:1-578:49" dir="auto">
<a id="user-content-data-compression-library-zlib-version-125" class="anchor" href="#data-compression-library-zlib-version-125" aria-hidden="true"></a>Data Compression Library (zlib) version 1.2.5</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright (C) 1995-2010 Jean-loup Gailly and Mark Adler</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">This software is provided \'as-is\', without any express or implied warranty. In no event will the authors be held liable for any damages arising from the use of this software.</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">Permission is granted to anyone to use this software for any purpose, including commercial applications, and to alter it and redistribute it freely, subject to the following restrictions:</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">1. The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment in the product documentation would be appreciated but is not required.</span>
<span id="LC9" class="line" lang="plaintext">2. Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.</span>
<span id="LC10" class="line" lang="plaintext">3. This notice may not be removed or altered from any source distribution.</span>
<span id="LC11" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="594:1-594:35" dir="auto">
<a id="user-content-sqlite-sqlite-version-3771" class="anchor" href="#sqlite-sqlite-version-3771" aria-hidden="true"></a>SQLite (sqlite) version 3.7.7.1</h3>
<p data-sourcepos="596:1-596:158" dir="auto">According to the comments in the source files, the code is in the public domain. See the SQLite Copyright page on the SQLite web site for further information.</p>
<h3 data-sourcepos="598:1-598:10" dir="auto">
<a id="user-content-webrtc" class="anchor" href="#webrtc" aria-hidden="true"></a>WebRTC</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright (c) 2011, The WebRTC project authors. All rights reserved.</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Redistribution and use in source and binary forms, with or without</span>
<span id="LC5" class="line" lang="plaintext">modification, are permitted provided that the following conditions are</span>
<span id="LC6" class="line" lang="plaintext">met:</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">  * Redistributions of source code must retain the above copyright</span>
<span id="LC9" class="line" lang="plaintext">    notice, this list of conditions and the following disclaimer.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">  * Redistributions in binary form must reproduce the above copyright</span>
<span id="LC12" class="line" lang="plaintext">    notice, this list of conditions and the following disclaimer in</span>
<span id="LC13" class="line" lang="plaintext">    the documentation and/or other materials provided with the</span>
<span id="LC14" class="line" lang="plaintext">    distribution.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext">  * Neither the name of Google nor the names of its contributors may</span>
<span id="LC17" class="line" lang="plaintext">    be used to endorse or promote products derived from this software</span>
<span id="LC18" class="line" lang="plaintext">    without specific prior written permission.</span>
<span id="LC19" class="line" lang="plaintext"></span>
<span id="LC20" class="line" lang="plaintext">THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS</span>
<span id="LC21" class="line" lang="plaintext">"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT</span>
<span id="LC22" class="line" lang="plaintext">LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR</span>
<span id="LC23" class="line" lang="plaintext">A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT</span>
<span id="LC24" class="line" lang="plaintext">HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,</span>
<span id="LC25" class="line" lang="plaintext">SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT</span>
<span id="LC26" class="line" lang="plaintext">LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,</span>
<span id="LC27" class="line" lang="plaintext">DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY</span>
<span id="LC28" class="line" lang="plaintext">THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT</span>
<span id="LC29" class="line" lang="plaintext">(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE</span>
<span id="LC30" class="line" lang="plaintext">OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</span>
<span id="LC31" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="634:1-634:7" dir="auto">
<a id="user-content-sip" class="anchor" href="#sip" aria-hidden="true"></a>SIP</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">RIVERBANK COMPUTING LIMITED LICENSE AGREEMENT FOR SIP</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">1. This LICENSE AGREEMENT is between Riverbank Computing Limited ("Riverbank"),</span>
<span id="LC5" class="line" lang="plaintext">and the Individual or Organization ("Licensee") accessing and otherwise using</span>
<span id="LC6" class="line" lang="plaintext">SIP software in source or binary form and its associated documentation.  SIP</span>
<span id="LC7" class="line" lang="plaintext">comprises a software tool for generating Python bindings for software C and C++</span>
<span id="LC8" class="line" lang="plaintext">libraries, and a Python extension module used at runtime by those generated</span>
<span id="LC9" class="line" lang="plaintext">bindings.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">2. Subject to the terms and conditions of this License Agreement, Riverbank</span>
<span id="LC12" class="line" lang="plaintext">hereby grants Licensee a nonexclusive, royalty-free, world-wide license to</span>
<span id="LC13" class="line" lang="plaintext">reproduce, analyze, test, perform and/or display publicly, prepare derivative</span>
<span id="LC14" class="line" lang="plaintext">works, distribute, and otherwise use SIP alone or in any derivative version,</span>
<span id="LC15" class="line" lang="plaintext">provided, however, that Riverbank\'s License Agreement and Riverbank\'s notice of</span>
<span id="LC16" class="line" lang="plaintext">copyright, e.g., "Copyright (c) 2015 Riverbank Computing Limited; All Rights</span>
<span id="LC17" class="line" lang="plaintext">Reserved" are retained in SIP alone or in any derivative version prepared by</span>
<span id="LC18" class="line" lang="plaintext">Licensee.</span>
<span id="LC19" class="line" lang="plaintext"></span>
<span id="LC20" class="line" lang="plaintext">3. In the event Licensee prepares a derivative work that is based on or</span>
<span id="LC21" class="line" lang="plaintext">incorporates SIP or any part thereof, and wants to make the derivative work</span>
<span id="LC22" class="line" lang="plaintext">available to others as provided herein, then Licensee hereby agrees to include</span>
<span id="LC23" class="line" lang="plaintext">in any such work a brief summary of the changes made to SIP.</span>
<span id="LC24" class="line" lang="plaintext"></span>
<span id="LC25" class="line" lang="plaintext">4. Licensee may not use SIP to generate Python bindings for any C or C++</span>
<span id="LC26" class="line" lang="plaintext">library for which bindings are already provided by Riverbank.</span>
<span id="LC27" class="line" lang="plaintext"></span>
<span id="LC28" class="line" lang="plaintext">5. Riverbank is making SIP available to Licensee on an "AS IS" basis.</span>
<span id="LC29" class="line" lang="plaintext">RIVERBANK MAKES NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED.  BY WAY</span>
<span id="LC30" class="line" lang="plaintext">OF EXAMPLE, BUT NOT LIMITATION, RIVERBANK MAKES NO AND DISCLAIMS ANY</span>
<span id="LC31" class="line" lang="plaintext">REPRESENTATION OR WARRANTY OF MERCHANTABILITY OR FITNESS FOR ANY PARTICULAR</span>
<span id="LC32" class="line" lang="plaintext">PURPOSE OR THAT THE USE OF SIP WILL NOT INFRINGE ANY THIRD PARTY RIGHTS.</span>
<span id="LC33" class="line" lang="plaintext"></span>
<span id="LC34" class="line" lang="plaintext">6. RIVERBANK SHALL NOT BE LIABLE TO LICENSEE OR ANY OTHER USERS OF SIP FOR ANY</span>
<span id="LC35" class="line" lang="plaintext">INCIDENTAL, SPECIAL, OR CONSEQUENTIAL DAMAGES OR LOSS AS A RESULT OF MODIFYING,</span>
<span id="LC36" class="line" lang="plaintext">DISTRIBUTING, OR OTHERWISE USING SIP, OR ANY DERIVATIVE THEREOF, EVEN IF</span>
<span id="LC37" class="line" lang="plaintext">ADVISED OF THE POSSIBILITY THEREOF.</span>
<span id="LC38" class="line" lang="plaintext"></span>
<span id="LC39" class="line" lang="plaintext">7. This License Agreement will automatically terminate upon a material breach</span>
<span id="LC40" class="line" lang="plaintext">of its terms and conditions.</span>
<span id="LC41" class="line" lang="plaintext"></span>
<span id="LC42" class="line" lang="plaintext">8. Nothing in this License Agreement shall be deemed to create any relationship</span>
<span id="LC43" class="line" lang="plaintext">of agency, partnership, or joint venture between Riverbank and Licensee.  This</span>
<span id="LC44" class="line" lang="plaintext">License Agreement does not grant permission to use Riverbank trademarks or</span>
<span id="LC45" class="line" lang="plaintext">trade name in a trademark sense to endorse or promote products or services of</span>
<span id="LC46" class="line" lang="plaintext">Licensee, or any third party.</span>
<span id="LC47" class="line" lang="plaintext"></span>
<span id="LC48" class="line" lang="plaintext">9. By copying, installing or otherwise using SIP, Licensee agrees to be bound</span>
<span id="LC49" class="line" lang="plaintext">by the terms and conditions of this License Agreement.</span>
<span id="LC50" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="689:1-689:15" dir="auto">
<a id="user-content-pyinstaller" class="anchor" href="#pyinstaller" aria-hidden="true"></a>PyInstaller</h3>
<p data-sourcepos="691:1-691:370" dir="auto">PyInstaller is distributed under the GPL license (see the file COPYING.txt in the source code), with a special exception which allows to use PyInstaller to build and distribute non-free programs (including commercial ones). In other words, you have no restrictions in using PyInstaller as-is, but any kind of modifications to it will have to comply with the GPL license.</p>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">================================</span>
<span id="LC3" class="line" lang="plaintext"> The PyInstaller licensing terms</span>
<span id="LC4" class="line" lang="plaintext">================================</span>
<span id="LC5" class="line" lang="plaintext"> </span>
<span id="LC6" class="line" lang="plaintext"></span>
<span id="LC7" class="line" lang="plaintext">Copyright (c) 2010-2017, PyInstaller Development Team</span>
<span id="LC8" class="line" lang="plaintext">Copyright (c) 2005-2009, Giovanni Bajo</span>
<span id="LC9" class="line" lang="plaintext">Based on previous work under copyright (c) 2002 McMillan Enterprises, Inc.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext"></span>
<span id="LC12" class="line" lang="plaintext">PyInstaller is licensed under the terms of the GNU General Public License</span>
<span id="LC13" class="line" lang="plaintext">as published by the Free Software Foundation; either version 2 of the License,</span>
<span id="LC14" class="line" lang="plaintext">or (at your option) any later version.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext"></span>
<span id="LC17" class="line" lang="plaintext">Bootloader Exception</span>
<span id="LC18" class="line" lang="plaintext">--------------------</span>
<span id="LC19" class="line" lang="plaintext"></span>
<span id="LC20" class="line" lang="plaintext">In addition to the permissions in the GNU General Public License, the</span>
<span id="LC21" class="line" lang="plaintext">authors give you unlimited permission to link or embed compiled bootloader</span>
<span id="LC22" class="line" lang="plaintext">and related files into combinations with other programs, and to distribute</span>
<span id="LC23" class="line" lang="plaintext">those combinations without any restriction coming from the use of those</span>
<span id="LC24" class="line" lang="plaintext">files. (The General Public License restrictions do apply in other respects;</span>
<span id="LC25" class="line" lang="plaintext">for example, they cover modification of the files, and distribution when</span>
<span id="LC26" class="line" lang="plaintext">not linked into a combine executable.)</span>
<span id="LC27" class="line" lang="plaintext"> </span>
<span id="LC28" class="line" lang="plaintext"> </span>
<span id="LC29" class="line" lang="plaintext">Bootloader and Related Files</span>
<span id="LC30" class="line" lang="plaintext">----------------------------</span>
<span id="LC31" class="line" lang="plaintext"></span>
<span id="LC32" class="line" lang="plaintext">Bootloader and related files are files which are embedded within the</span>
<span id="LC33" class="line" lang="plaintext">final executable. This includes files in directories:</span>
<span id="LC34" class="line" lang="plaintext"></span>
<span id="LC35" class="line" lang="plaintext">./bootloader/</span>
<span id="LC36" class="line" lang="plaintext">./PyInstaller/loader</span>
<span id="LC37" class="line" lang="plaintext"></span>
<span id="LC38" class="line" lang="plaintext"> </span>
<span id="LC39" class="line" lang="plaintext">About the PyInstaller Development Team</span>
<span id="LC40" class="line" lang="plaintext">--------------------------------------</span>
<span id="LC41" class="line" lang="plaintext"></span>
<span id="LC42" class="line" lang="plaintext">The PyInstaller Development Team is the set of contributors</span>
<span id="LC43" class="line" lang="plaintext">to the PyInstaller project. A full list with details is kept</span>
<span id="LC44" class="line" lang="plaintext">in the documentation directory, in the file</span>
<span id="LC45" class="line" lang="plaintext">``doc/CREDITS.rst``.</span>
<span id="LC46" class="line" lang="plaintext"></span>
<span id="LC47" class="line" lang="plaintext">The core team that coordinates development on GitHub can be found here:</span>
<span id="LC48" class="line" lang="plaintext">https://github.com/pyinstaller/pyinstaller.  As of 2015, it consists of:</span>
<span id="LC49" class="line" lang="plaintext"></span>
<span id="LC50" class="line" lang="plaintext">* Hartmut Goebel</span>
<span id="LC51" class="line" lang="plaintext">* Martin Zibricky</span>
<span id="LC52" class="line" lang="plaintext">* David Vierra</span>
<span id="LC53" class="line" lang="plaintext">* David Cortesi</span>
<span id="LC54" class="line" lang="plaintext"></span>
<span id="LC55" class="line" lang="plaintext"></span>
<span id="LC56" class="line" lang="plaintext">Our Copyright Policy</span>
<span id="LC57" class="line" lang="plaintext">--------------------</span>
<span id="LC58" class="line" lang="plaintext"></span>
<span id="LC59" class="line" lang="plaintext">PyInstaller uses a shared copyright model. Each contributor maintains copyright</span>
<span id="LC60" class="line" lang="plaintext">over their contributions to PyInstaller. But, it is important to note that these</span>
<span id="LC61" class="line" lang="plaintext">contributions are typically only changes to the repositories. Thus,</span>
<span id="LC62" class="line" lang="plaintext">the PyInstaller source code, in its entirety is not the copyright of any single</span>
<span id="LC63" class="line" lang="plaintext">person or institution.  Instead, it is the collective copyright of the entire</span>
<span id="LC64" class="line" lang="plaintext">PyInstaller Development Team.  If individual contributors want to maintain</span>
<span id="LC65" class="line" lang="plaintext">a record of what changes/contributions they have specific copyright on, they</span>
<span id="LC66" class="line" lang="plaintext">should indicate their copyright in the commit message of the change, when they</span>
<span id="LC67" class="line" lang="plaintext">commit the change to the PyInstaller repository.</span>
<span id="LC68" class="line" lang="plaintext"></span>
<span id="LC69" class="line" lang="plaintext">With this in mind, the following banner should be used in any source code file</span>
<span id="LC70" class="line" lang="plaintext">to indicate the copyright and license terms:</span>
<span id="LC71" class="line" lang="plaintext"></span>
<span id="LC72" class="line" lang="plaintext"></span>
<span id="LC73" class="line" lang="plaintext">#-----------------------------------------------------------------------------</span>
<span id="LC74" class="line" lang="plaintext"># Copyright (c) 2005-20l5, PyInstaller Development Team.</span>
<span id="LC75" class="line" lang="plaintext">#</span>
<span id="LC76" class="line" lang="plaintext"># Distributed under the terms of the GNU General Public License with exception</span>
<span id="LC77" class="line" lang="plaintext"># for distributing bootloader.</span>
<span id="LC78" class="line" lang="plaintext">#</span>
<span id="LC79" class="line" lang="plaintext"># The full license is in the file COPYING.txt, distributed with this software.</span>
<span id="LC80" class="line" lang="plaintext">#-----------------------------------------------------------------------------</span>
<span id="LC81" class="line" lang="plaintext"></span>
<span id="LC82" class="line" lang="plaintext"></span>
<span id="LC83" class="line" lang="plaintext"></span>
<span id="LC84" class="line" lang="plaintext">GNU General Public License</span>
<span id="LC85" class="line" lang="plaintext">--------------------------</span>
<span id="LC86" class="line" lang="plaintext"></span>
<span id="LC87" class="line" lang="plaintext">https://gnu.org/licenses/gpl-2.0.html</span>
<span id="LC88" class="line" lang="plaintext"></span>
<span id="LC89" class="line" lang="plaintext"></span>
<span id="LC90" class="line" lang="plaintext">		    GNU GENERAL PUBLIC LICENSE</span>
<span id="LC91" class="line" lang="plaintext">		       Version 2, June 1991</span>
<span id="LC92" class="line" lang="plaintext"></span>
<span id="LC93" class="line" lang="plaintext"> Copyright (C) 1989, 1991 Free Software Foundation, Inc.</span>
<span id="LC94" class="line" lang="plaintext">                 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA</span>
<span id="LC95" class="line" lang="plaintext"> Everyone is permitted to copy and distribute verbatim copies</span>
<span id="LC96" class="line" lang="plaintext"> of this license document, but changing it is not allowed.</span>
<span id="LC97" class="line" lang="plaintext"></span>
<span id="LC98" class="line" lang="plaintext">			    Preamble</span>
<span id="LC99" class="line" lang="plaintext"></span>
<span id="LC100" class="line" lang="plaintext">  The licenses for most software are designed to take away your</span>
<span id="LC101" class="line" lang="plaintext">freedom to share and change it.  By contrast, the GNU General Public</span>
<span id="LC102" class="line" lang="plaintext">License is intended to guarantee your freedom to share and change free</span>
<span id="LC103" class="line" lang="plaintext">software--to make sure the software is free for all its users.  This</span>
<span id="LC104" class="line" lang="plaintext">General Public License applies to most of the Free Software</span>
<span id="LC105" class="line" lang="plaintext">Foundation\'s software and to any other program whose authors commit to</span>
<span id="LC106" class="line" lang="plaintext">using it.  (Some other Free Software Foundation software is covered by</span>
<span id="LC107" class="line" lang="plaintext">the GNU Library General Public License instead.)  You can apply it to</span>
<span id="LC108" class="line" lang="plaintext">your programs, too.</span>
<span id="LC109" class="line" lang="plaintext"></span>
<span id="LC110" class="line" lang="plaintext">  When we speak of free software, we are referring to freedom, not</span>
<span id="LC111" class="line" lang="plaintext">price.  Our General Public Licenses are designed to make sure that you</span>
<span id="LC112" class="line" lang="plaintext">have the freedom to distribute copies of free software (and charge for</span>
<span id="LC113" class="line" lang="plaintext">this service if you wish), that you receive source code or can get it</span>
<span id="LC114" class="line" lang="plaintext">if you want it, that you can change the software or use pieces of it</span>
<span id="LC115" class="line" lang="plaintext">in new free programs; and that you know you can do these things.</span>
<span id="LC116" class="line" lang="plaintext"></span>
<span id="LC117" class="line" lang="plaintext">  To protect your rights, we need to make restrictions that forbid</span>
<span id="LC118" class="line" lang="plaintext">anyone to deny you these rights or to ask you to surrender the rights.</span>
<span id="LC119" class="line" lang="plaintext">These restrictions translate to certain responsibilities for you if you</span>
<span id="LC120" class="line" lang="plaintext">distribute copies of the software, or if you modify it.</span>
<span id="LC121" class="line" lang="plaintext"></span>
<span id="LC122" class="line" lang="plaintext">  For example, if you distribute copies of such a program, whether</span>
<span id="LC123" class="line" lang="plaintext">gratis or for a fee, you must give the recipients all the rights that</span>
<span id="LC124" class="line" lang="plaintext">you have.  You must make sure that they, too, receive or can get the</span>
<span id="LC125" class="line" lang="plaintext">source code.  And you must show them these terms so they know their</span>
<span id="LC126" class="line" lang="plaintext">rights.</span>
<span id="LC127" class="line" lang="plaintext"></span>
<span id="LC128" class="line" lang="plaintext">  We protect your rights with two steps: (1) copyright the software, and</span>
<span id="LC129" class="line" lang="plaintext">(2) offer you this license which gives you legal permission to copy,</span>
<span id="LC130" class="line" lang="plaintext">distribute and/or modify the software.</span>
<span id="LC131" class="line" lang="plaintext"></span>
<span id="LC132" class="line" lang="plaintext">  Also, for each author\'s protection and ours, we want to make certain</span>
<span id="LC133" class="line" lang="plaintext">that everyone understands that there is no warranty for this free</span>
<span id="LC134" class="line" lang="plaintext">software.  If the software is modified by someone else and passed on, we</span>
<span id="LC135" class="line" lang="plaintext">want its recipients to know that what they have is not the original, so</span>
<span id="LC136" class="line" lang="plaintext">that any problems introduced by others will not reflect on the original</span>
<span id="LC137" class="line" lang="plaintext">authors\' reputations.</span>
<span id="LC138" class="line" lang="plaintext"></span>
<span id="LC139" class="line" lang="plaintext">  Finally, any free program is threatened constantly by software</span>
<span id="LC140" class="line" lang="plaintext">patents.  We wish to avoid the danger that redistributors of a free</span>
<span id="LC141" class="line" lang="plaintext">program will individually obtain patent licenses, in effect making the</span>
<span id="LC142" class="line" lang="plaintext">program proprietary.  To prevent this, we have made it clear that any</span>
<span id="LC143" class="line" lang="plaintext">patent must be licensed for everyone\'s free use or not licensed at all.</span>
<span id="LC144" class="line" lang="plaintext"></span>
<span id="LC145" class="line" lang="plaintext">  The precise terms and conditions for copying, distribution and</span>
<span id="LC146" class="line" lang="plaintext">modification follow.</span>
<span id="LC147" class="line" lang="plaintext"></span>
<span id="LC148" class="line" lang="plaintext">		    GNU GENERAL PUBLIC LICENSE</span>
<span id="LC149" class="line" lang="plaintext">   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION</span>
<span id="LC150" class="line" lang="plaintext"></span>
<span id="LC151" class="line" lang="plaintext">  0. This License applies to any program or other work which contains</span>
<span id="LC152" class="line" lang="plaintext">a notice placed by the copyright holder saying it may be distributed</span>
<span id="LC153" class="line" lang="plaintext">under the terms of this General Public License.  The "Program", below,</span>
<span id="LC154" class="line" lang="plaintext">refers to any such program or work, and a "work based on the Program"</span>
<span id="LC155" class="line" lang="plaintext">means either the Program or any derivative work under copyright law:</span>
<span id="LC156" class="line" lang="plaintext">that is to say, a work containing the Program or a portion of it,</span>
<span id="LC157" class="line" lang="plaintext">either verbatim or with modifications and/or translated into another</span>
<span id="LC158" class="line" lang="plaintext">language.  (Hereinafter, translation is included without limitation in</span>
<span id="LC159" class="line" lang="plaintext">the term "modification".)  Each licensee is addressed as "you".</span>
<span id="LC160" class="line" lang="plaintext"></span>
<span id="LC161" class="line" lang="plaintext">Activities other than copying, distribution and modification are not</span>
<span id="LC162" class="line" lang="plaintext">covered by this License; they are outside its scope.  The act of</span>
<span id="LC163" class="line" lang="plaintext">running the Program is not restricted, and the output from the Program</span>
<span id="LC164" class="line" lang="plaintext">is covered only if its contents constitute a work based on the</span>
<span id="LC165" class="line" lang="plaintext">Program (independent of having been made by running the Program).</span>
<span id="LC166" class="line" lang="plaintext">Whether that is true depends on what the Program does.</span>
<span id="LC167" class="line" lang="plaintext"></span>
<span id="LC168" class="line" lang="plaintext">  1. You may copy and distribute verbatim copies of the Program\'s</span>
<span id="LC169" class="line" lang="plaintext">source code as you receive it, in any medium, provided that you</span>
<span id="LC170" class="line" lang="plaintext">conspicuously and appropriately publish on each copy an appropriate</span>
<span id="LC171" class="line" lang="plaintext">copyright notice and disclaimer of warranty; keep intact all the</span>
<span id="LC172" class="line" lang="plaintext">notices that refer to this License and to the absence of any warranty;</span>
<span id="LC173" class="line" lang="plaintext">and give any other recipients of the Program a copy of this License</span>
<span id="LC174" class="line" lang="plaintext">along with the Program.</span>
<span id="LC175" class="line" lang="plaintext"></span>
<span id="LC176" class="line" lang="plaintext">You may charge a fee for the physical act of transferring a copy, and</span>
<span id="LC177" class="line" lang="plaintext">you may at your option offer warranty protection in exchange for a fee.</span>
<span id="LC178" class="line" lang="plaintext"></span>
<span id="LC179" class="line" lang="plaintext">  2. You may modify your copy or copies of the Program or any portion</span>
<span id="LC180" class="line" lang="plaintext">of it, thus forming a work based on the Program, and copy and</span>
<span id="LC181" class="line" lang="plaintext">distribute such modifications or work under the terms of Section 1</span>
<span id="LC182" class="line" lang="plaintext">above, provided that you also meet all of these conditions:</span>
<span id="LC183" class="line" lang="plaintext"></span>
<span id="LC184" class="line" lang="plaintext">    a) You must cause the modified files to carry prominent notices</span>
<span id="LC185" class="line" lang="plaintext">    stating that you changed the files and the date of any change.</span>
<span id="LC186" class="line" lang="plaintext"></span>
<span id="LC187" class="line" lang="plaintext">    b) You must cause any work that you distribute or publish, that in</span>
<span id="LC188" class="line" lang="plaintext">    whole or in part contains or is derived from the Program or any</span>
<span id="LC189" class="line" lang="plaintext">    part thereof, to be licensed as a whole at no charge to all third</span>
<span id="LC190" class="line" lang="plaintext">    parties under the terms of this License.</span>
<span id="LC191" class="line" lang="plaintext"></span>
<span id="LC192" class="line" lang="plaintext">    c) If the modified program normally reads commands interactively</span>
<span id="LC193" class="line" lang="plaintext">    when run, you must cause it, when started running for such</span>
<span id="LC194" class="line" lang="plaintext">    interactive use in the most ordinary way, to print or display an</span>
<span id="LC195" class="line" lang="plaintext">    announcement including an appropriate copyright notice and a</span>
<span id="LC196" class="line" lang="plaintext">    notice that there is no warranty (or else, saying that you provide</span>
<span id="LC197" class="line" lang="plaintext">    a warranty) and that users may redistribute the program under</span>
<span id="LC198" class="line" lang="plaintext">    these conditions, and telling the user how to view a copy of this</span>
<span id="LC199" class="line" lang="plaintext">    License.  (Exception: if the Program itself is interactive but</span>
<span id="LC200" class="line" lang="plaintext">    does not normally print such an announcement, your work based on</span>
<span id="LC201" class="line" lang="plaintext">    the Program is not required to print an announcement.)</span>
<span id="LC202" class="line" lang="plaintext"></span>
<span id="LC203" class="line" lang="plaintext">These requirements apply to the modified work as a whole.  If</span>
<span id="LC204" class="line" lang="plaintext">identifiable sections of that work are not derived from the Program,</span>
<span id="LC205" class="line" lang="plaintext">and can be reasonably considered independent and separate works in</span>
<span id="LC206" class="line" lang="plaintext">themselves, then this License, and its terms, do not apply to those</span>
<span id="LC207" class="line" lang="plaintext">sections when you distribute them as separate works.  But when you</span>
<span id="LC208" class="line" lang="plaintext">distribute the same sections as part of a whole which is a work based</span>
<span id="LC209" class="line" lang="plaintext">on the Program, the distribution of the whole must be on the terms of</span>
<span id="LC210" class="line" lang="plaintext">this License, whose permissions for other licensees extend to the</span>
<span id="LC211" class="line" lang="plaintext">entire whole, and thus to each and every part regardless of who wrote it.</span>
<span id="LC212" class="line" lang="plaintext"></span>
<span id="LC213" class="line" lang="plaintext">Thus, it is not the intent of this section to claim rights or contest</span>
<span id="LC214" class="line" lang="plaintext">your rights to work written entirely by you; rather, the intent is to</span>
<span id="LC215" class="line" lang="plaintext">exercise the right to control the distribution of derivative or</span>
<span id="LC216" class="line" lang="plaintext">collective works based on the Program.</span>
<span id="LC217" class="line" lang="plaintext"></span>
<span id="LC218" class="line" lang="plaintext">In addition, mere aggregation of another work not based on the Program</span>
<span id="LC219" class="line" lang="plaintext">with the Program (or with a work based on the Program) on a volume of</span>
<span id="LC220" class="line" lang="plaintext">a storage or distribution medium does not bring the other work under</span>
<span id="LC221" class="line" lang="plaintext">the scope of this License.</span>
<span id="LC222" class="line" lang="plaintext"></span>
<span id="LC223" class="line" lang="plaintext">  3. You may copy and distribute the Program (or a work based on it,</span>
<span id="LC224" class="line" lang="plaintext">under Section 2) in object code or executable form under the terms of</span>
<span id="LC225" class="line" lang="plaintext">Sections 1 and 2 above provided that you also do one of the following:</span>
<span id="LC226" class="line" lang="plaintext"></span>
<span id="LC227" class="line" lang="plaintext">    a) Accompany it with the complete corresponding machine-readable</span>
<span id="LC228" class="line" lang="plaintext">    source code, which must be distributed under the terms of Sections</span>
<span id="LC229" class="line" lang="plaintext">    1 and 2 above on a medium customarily used for software interchange; or,</span>
<span id="LC230" class="line" lang="plaintext"></span>
<span id="LC231" class="line" lang="plaintext">    b) Accompany it with a written offer, valid for at least three</span>
<span id="LC232" class="line" lang="plaintext">    years, to give any third party, for a charge no more than your</span>
<span id="LC233" class="line" lang="plaintext">    cost of physically performing source distribution, a complete</span>
<span id="LC234" class="line" lang="plaintext">    machine-readable copy of the corresponding source code, to be</span>
<span id="LC235" class="line" lang="plaintext">    distributed under the terms of Sections 1 and 2 above on a medium</span>
<span id="LC236" class="line" lang="plaintext">    customarily used for software interchange; or,</span>
<span id="LC237" class="line" lang="plaintext"></span>
<span id="LC238" class="line" lang="plaintext">    c) Accompany it with the information you received as to the offer</span>
<span id="LC239" class="line" lang="plaintext">    to distribute corresponding source code.  (This alternative is</span>
<span id="LC240" class="line" lang="plaintext">    allowed only for noncommercial distribution and only if you</span>
<span id="LC241" class="line" lang="plaintext">    received the program in object code or executable form with such</span>
<span id="LC242" class="line" lang="plaintext">    an offer, in accord with Subsection b above.)</span>
<span id="LC243" class="line" lang="plaintext"></span>
<span id="LC244" class="line" lang="plaintext">The source code for a work means the preferred form of the work for</span>
<span id="LC245" class="line" lang="plaintext">making modifications to it.  For an executable work, complete source</span>
<span id="LC246" class="line" lang="plaintext">code means all the source code for all modules it contains, plus any</span>
<span id="LC247" class="line" lang="plaintext">associated interface definition files, plus the scripts used to</span>
<span id="LC248" class="line" lang="plaintext">control compilation and installation of the executable.  However, as a</span>
<span id="LC249" class="line" lang="plaintext">special exception, the source code distributed need not include</span>
<span id="LC250" class="line" lang="plaintext">anything that is normally distributed (in either source or binary</span>
<span id="LC251" class="line" lang="plaintext">form) with the major components (compiler, kernel, and so on) of the</span>
<span id="LC252" class="line" lang="plaintext">operating system on which the executable runs, unless that component</span>
<span id="LC253" class="line" lang="plaintext">itself accompanies the executable.</span>
<span id="LC254" class="line" lang="plaintext"></span>
<span id="LC255" class="line" lang="plaintext">If distribution of executable or object code is made by offering</span>
<span id="LC256" class="line" lang="plaintext">access to copy from a designated place, then offering equivalent</span>
<span id="LC257" class="line" lang="plaintext">access to copy the source code from the same place counts as</span>
<span id="LC258" class="line" lang="plaintext">distribution of the source code, even though third parties are not</span>
<span id="LC259" class="line" lang="plaintext">compelled to copy the source along with the object code.</span>
<span id="LC260" class="line" lang="plaintext"></span>
<span id="LC261" class="line" lang="plaintext">  4. You may not copy, modify, sublicense, or distribute the Program</span>
<span id="LC262" class="line" lang="plaintext">except as expressly provided under this License.  Any attempt</span>
<span id="LC263" class="line" lang="plaintext">otherwise to copy, modify, sublicense or distribute the Program is</span>
<span id="LC264" class="line" lang="plaintext">void, and will automatically terminate your rights under this License.</span>
<span id="LC265" class="line" lang="plaintext">However, parties who have received copies, or rights, from you under</span>
<span id="LC266" class="line" lang="plaintext">this License will not have their licenses terminated so long as such</span>
<span id="LC267" class="line" lang="plaintext">parties remain in full compliance.</span>
<span id="LC268" class="line" lang="plaintext"></span>
<span id="LC269" class="line" lang="plaintext">  5. You are not required to accept this License, since you have not</span>
<span id="LC270" class="line" lang="plaintext">signed it.  However, nothing else grants you permission to modify or</span>
<span id="LC271" class="line" lang="plaintext">distribute the Program or its derivative works.  These actions are</span>
<span id="LC272" class="line" lang="plaintext">prohibited by law if you do not accept this License.  Therefore, by</span>
<span id="LC273" class="line" lang="plaintext">modifying or distributing the Program (or any work based on the</span>
<span id="LC274" class="line" lang="plaintext">Program), you indicate your acceptance of this License to do so, and</span>
<span id="LC275" class="line" lang="plaintext">all its terms and conditions for copying, distributing or modifying</span>
<span id="LC276" class="line" lang="plaintext">the Program or works based on it.</span>
<span id="LC277" class="line" lang="plaintext"></span>
<span id="LC278" class="line" lang="plaintext">  6. Each time you redistribute the Program (or any work based on the</span>
<span id="LC279" class="line" lang="plaintext">Program), the recipient automatically receives a license from the</span>
<span id="LC280" class="line" lang="plaintext">original licensor to copy, distribute or modify the Program subject to</span>
<span id="LC281" class="line" lang="plaintext">these terms and conditions.  You may not impose any further</span>
<span id="LC282" class="line" lang="plaintext">restrictions on the recipients\' exercise of the rights granted herein.</span>
<span id="LC283" class="line" lang="plaintext">You are not responsible for enforcing compliance by third parties to</span>
<span id="LC284" class="line" lang="plaintext">this License.</span>
<span id="LC285" class="line" lang="plaintext"></span>
<span id="LC286" class="line" lang="plaintext">  7. If, as a consequence of a court judgment or allegation of patent</span>
<span id="LC287" class="line" lang="plaintext">infringement or for any other reason (not limited to patent issues),</span>
<span id="LC288" class="line" lang="plaintext">conditions are imposed on you (whether by court order, agreement or</span>
<span id="LC289" class="line" lang="plaintext">otherwise) that contradict the conditions of this License, they do not</span>
<span id="LC290" class="line" lang="plaintext">excuse you from the conditions of this License.  If you cannot</span>
<span id="LC291" class="line" lang="plaintext">distribute so as to satisfy simultaneously your obligations under this</span>
<span id="LC292" class="line" lang="plaintext">License and any other pertinent obligations, then as a consequence you</span>
<span id="LC293" class="line" lang="plaintext">may not distribute the Program at all.  For example, if a patent</span>
<span id="LC294" class="line" lang="plaintext">license would not permit royalty-free redistribution of the Program by</span>
<span id="LC295" class="line" lang="plaintext">all those who receive copies directly or indirectly through you, then</span>
<span id="LC296" class="line" lang="plaintext">the only way you could satisfy both it and this License would be to</span>
<span id="LC297" class="line" lang="plaintext">refrain entirely from distribution of the Program.</span>
<span id="LC298" class="line" lang="plaintext"></span>
<span id="LC299" class="line" lang="plaintext">If any portion of this section is held invalid or unenforceable under</span>
<span id="LC300" class="line" lang="plaintext">any particular circumstance, the balance of the section is intended to</span>
<span id="LC301" class="line" lang="plaintext">apply and the section as a whole is intended to apply in other</span>
<span id="LC302" class="line" lang="plaintext">circumstances.</span>
<span id="LC303" class="line" lang="plaintext"></span>
<span id="LC304" class="line" lang="plaintext">It is not the purpose of this section to induce you to infringe any</span>
<span id="LC305" class="line" lang="plaintext">patents or other property right claims or to contest validity of any</span>
<span id="LC306" class="line" lang="plaintext">such claims; this section has the sole purpose of protecting the</span>
<span id="LC307" class="line" lang="plaintext">integrity of the free software distribution system, which is</span>
<span id="LC308" class="line" lang="plaintext">implemented by public license practices.  Many people have made</span>
<span id="LC309" class="line" lang="plaintext">generous contributions to the wide range of software distributed</span>
<span id="LC310" class="line" lang="plaintext">through that system in reliance on consistent application of that</span>
<span id="LC311" class="line" lang="plaintext">system; it is up to the author/donor to decide if he or she is willing</span>
<span id="LC312" class="line" lang="plaintext">to distribute software through any other system and a licensee cannot</span>
<span id="LC313" class="line" lang="plaintext">impose that choice.</span>
<span id="LC314" class="line" lang="plaintext"></span>
<span id="LC315" class="line" lang="plaintext">This section is intended to make thoroughly clear what is believed to</span>
<span id="LC316" class="line" lang="plaintext">be a consequence of the rest of this License.</span>
<span id="LC317" class="line" lang="plaintext"></span>
<span id="LC318" class="line" lang="plaintext">  8. If the distribution and/or use of the Program is restricted in</span>
<span id="LC319" class="line" lang="plaintext">certain countries either by patents or by copyrighted interfaces, the</span>
<span id="LC320" class="line" lang="plaintext">original copyright holder who places the Program under this License</span>
<span id="LC321" class="line" lang="plaintext">may add an explicit geographical distribution limitation excluding</span>
<span id="LC322" class="line" lang="plaintext">those countries, so that distribution is permitted only in or among</span>
<span id="LC323" class="line" lang="plaintext">countries not thus excluded.  In such case, this License incorporates</span>
<span id="LC324" class="line" lang="plaintext">the limitation as if written in the body of this License.</span>
<span id="LC325" class="line" lang="plaintext"></span>
<span id="LC326" class="line" lang="plaintext">  9. The Free Software Foundation may publish revised and/or new versions</span>
<span id="LC327" class="line" lang="plaintext">of the General Public License from time to time.  Such new versions will</span>
<span id="LC328" class="line" lang="plaintext">be similar in spirit to the present version, but may differ in detail to</span>
<span id="LC329" class="line" lang="plaintext">address new problems or concerns.</span>
<span id="LC330" class="line" lang="plaintext"></span>
<span id="LC331" class="line" lang="plaintext">Each version is given a distinguishing version number.  If the Program</span>
<span id="LC332" class="line" lang="plaintext">specifies a version number of this License which applies to it and "any</span>
<span id="LC333" class="line" lang="plaintext">later version", you have the option of following the terms and conditions</span>
<span id="LC334" class="line" lang="plaintext">either of that version or of any later version published by the Free</span>
<span id="LC335" class="line" lang="plaintext">Software Foundation.  If the Program does not specify a version number of</span>
<span id="LC336" class="line" lang="plaintext">this License, you may choose any version ever published by the Free Software</span>
<span id="LC337" class="line" lang="plaintext">Foundation.</span>
<span id="LC338" class="line" lang="plaintext"></span>
<span id="LC339" class="line" lang="plaintext">  10. If you wish to incorporate parts of the Program into other free</span>
<span id="LC340" class="line" lang="plaintext">programs whose distribution conditions are different, write to the author</span>
<span id="LC341" class="line" lang="plaintext">to ask for permission.  For software which is copyrighted by the Free</span>
<span id="LC342" class="line" lang="plaintext">Software Foundation, write to the Free Software Foundation; we sometimes</span>
<span id="LC343" class="line" lang="plaintext">make exceptions for this.  Our decision will be guided by the two goals</span>
<span id="LC344" class="line" lang="plaintext">of preserving the free status of all derivatives of our free software and</span>
<span id="LC345" class="line" lang="plaintext">of promoting the sharing and reuse of software generally.</span>
<span id="LC346" class="line" lang="plaintext"></span>
<span id="LC347" class="line" lang="plaintext">			    NO WARRANTY</span>
<span id="LC348" class="line" lang="plaintext"></span>
<span id="LC349" class="line" lang="plaintext">  11. BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY</span>
<span id="LC350" class="line" lang="plaintext">FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW.  EXCEPT WHEN</span>
<span id="LC351" class="line" lang="plaintext">OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES</span>
<span id="LC352" class="line" lang="plaintext">PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED</span>
<span id="LC353" class="line" lang="plaintext">OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF</span>
<span id="LC354" class="line" lang="plaintext">MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.  THE ENTIRE RISK AS</span>
<span id="LC355" class="line" lang="plaintext">TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU.  SHOULD THE</span>
<span id="LC356" class="line" lang="plaintext">PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING,</span>
<span id="LC357" class="line" lang="plaintext">REPAIR OR CORRECTION.</span>
<span id="LC358" class="line" lang="plaintext"></span>
<span id="LC359" class="line" lang="plaintext">  12. IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING</span>
<span id="LC360" class="line" lang="plaintext">WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MAY MODIFY AND/OR</span>
<span id="LC361" class="line" lang="plaintext">REDISTRIBUTE THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES,</span>
<span id="LC362" class="line" lang="plaintext">INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING</span>
<span id="LC363" class="line" lang="plaintext">OUT OF THE USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED</span>
<span id="LC364" class="line" lang="plaintext">TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY</span>
<span id="LC365" class="line" lang="plaintext">YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER</span>
<span id="LC366" class="line" lang="plaintext">PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE</span>
<span id="LC367" class="line" lang="plaintext">POSSIBILITY OF SUCH DAMAGES.</span>
<span id="LC368" class="line" lang="plaintext"></span>
<span id="LC369" class="line" lang="plaintext">		     END OF TERMS AND CONDITIONS</span>
<span id="LC370" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1066:1-1066:12" dir="auto">
<a id="user-content-requests" class="anchor" href="#requests" aria-hidden="true"></a>requests</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright 2017 Kenneth Reitz</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">   Licensed under the Apache License, Version 2.0 (the "License");</span>
<span id="LC5" class="line" lang="plaintext">   you may not use this file except in compliance with the License.</span>
<span id="LC6" class="line" lang="plaintext">   You may obtain a copy of the License at</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">       http://www.apache.org/licenses/LICENSE-2.0</span>
<span id="LC9" class="line" lang="plaintext"></span>
<span id="LC10" class="line" lang="plaintext">   Unless required by applicable law or agreed to in writing, software</span>
<span id="LC11" class="line" lang="plaintext">   distributed under the License is distributed on an "AS IS" BASIS,</span>
<span id="LC12" class="line" lang="plaintext">   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.</span>
<span id="LC13" class="line" lang="plaintext">   See the License for the specific language governing permissions and</span>
<span id="LC14" class="line" lang="plaintext">   limitations under the License.</span>
<span id="LC15" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1086:1-1086:12" dir="auto">
<a id="user-content-filelock" class="anchor" href="#filelock" aria-hidden="true"></a>filelock</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">License</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">This is free and unencumbered software released into the public domain.</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">Anyone is free to copy, modify, publish, use, compile, sell, or distribute this software, either in source code form or as a compiled binary, for any purpose, commercial or non-commercial, and by any means.</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">In jurisdictions that recognize copyright laws, the author or authors of this software dedicate any and all copyright interest in the software to the public domain. We make this dedication for the benefit of the public at large and to the detriment of our heirs and successors. We intend this dedication to be an overt act of relinquishment in perpetuity of all present and future rights to this software under copyright law.</span>
<span id="LC9" class="line" lang="plaintext"></span>
<span id="LC10" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</span>
<span id="LC11" class="line" lang="plaintext"></span>
<span id="LC12" class="line" lang="plaintext">For more information, please refer to &lt;http://unlicense.org&gt;</span>
<span id="LC13" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1104:1-1104:14" dir="auto">
<a id="user-content-sqlalchemy" class="anchor" href="#sqlalchemy" aria-hidden="true"></a>SQLAlchemy</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">This is the MIT license: http://www.opensource.org/licenses/mit-license.php</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Copyright (c) 2005-2017 Michael Bayer and contributors. SQLAlchemy is a trademark of Michael Bayer.</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.</span>
<span id="LC9" class="line" lang="plaintext"></span>
<span id="LC10" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</span>
<span id="LC11" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1120:1-1120:18" dir="auto">
<a id="user-content-nanomsg-python" class="anchor" href="#nanomsg-python" aria-hidden="true"></a>nanomsg-python</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">The MIT License (MIT)</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Copyright (c) 2013 Tony Simpson</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy of</span>
<span id="LC7" class="line" lang="plaintext">this software and associated documentation files (the "Software"), to deal in</span>
<span id="LC8" class="line" lang="plaintext">the Software without restriction, including without limitation the rights to</span>
<span id="LC9" class="line" lang="plaintext">use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of</span>
<span id="LC10" class="line" lang="plaintext">the Software, and to permit persons to whom the Software is furnished to do so,</span>
<span id="LC11" class="line" lang="plaintext">subject to the following conditions:</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in all</span>
<span id="LC14" class="line" lang="plaintext">copies or substantial portions of the Software.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR</span>
<span id="LC17" class="line" lang="plaintext">IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS</span>
<span id="LC18" class="line" lang="plaintext">FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR</span>
<span id="LC19" class="line" lang="plaintext">COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER</span>
<span id="LC20" class="line" lang="plaintext">IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN</span>
<span id="LC21" class="line" lang="plaintext">CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</span>
<span id="LC22" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1147:1-1147:11" dir="auto">
<a id="user-content-nanomsg" class="anchor" href="#nanomsg" aria-hidden="true"></a>nanomsg</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">It is licensed under MIT/X11 license.</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">"nanomsg" is a trademark of Martin Sustrik</span>
<span id="LC5" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1157:1-1157:10" dir="auto">
<a id="user-content-pyobjc" class="anchor" href="#pyobjc" aria-hidden="true"></a>pyobjc</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext">(This is the MIT license)</span>
<span id="LC2" class="line" lang="plaintext"></span>
<span id="LC3" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:</span>
<span id="LC4" class="line" lang="plaintext"></span>
<span id="LC5" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.</span>
<span id="LC6" class="line" lang="plaintext"></span>
<span id="LC7" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</span>
<span id="LC8" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1170:1-1170:11" dir="auto">
<a id="user-content-pywin32" class="anchor" href="#pywin32" aria-hidden="true"></a>pywin32</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Unless stated in the specific source file, this work is</span>
<span id="LC3" class="line" lang="plaintext">Copyright (c) 1996-2008, Greg Stein and Mark Hammond.</span>
<span id="LC4" class="line" lang="plaintext">All rights reserved.</span>
<span id="LC5" class="line" lang="plaintext">Redistribution and use in source and binary forms, with or without </span>
<span id="LC6" class="line" lang="plaintext">modification, are permitted provided that the following conditions </span>
<span id="LC7" class="line" lang="plaintext">are met:</span>
<span id="LC8" class="line" lang="plaintext">Redistributions of source code must retain the above copyright notice, </span>
<span id="LC9" class="line" lang="plaintext">this list of conditions and the following disclaimer.</span>
<span id="LC10" class="line" lang="plaintext">Redistributions in binary form must reproduce the above copyright </span>
<span id="LC11" class="line" lang="plaintext">notice, this list of conditions and the following disclaimer in </span>
<span id="LC12" class="line" lang="plaintext">the documentation and/or other materials provided with the distribution.</span>
<span id="LC13" class="line" lang="plaintext">Neither names of Greg Stein, Mark Hammond nor the name of contributors may be used </span>
<span id="LC14" class="line" lang="plaintext">to endorse or promote products derived from this software without </span>
<span id="LC15" class="line" lang="plaintext">specific prior written permission. </span>
<span id="LC16" class="line" lang="plaintext">THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS</span>
<span id="LC17" class="line" lang="plaintext">IS\'\' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED</span>
<span id="LC18" class="line" lang="plaintext">TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A</span>
<span id="LC19" class="line" lang="plaintext">PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR</span>
<span id="LC20" class="line" lang="plaintext">CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,</span>
<span id="LC21" class="line" lang="plaintext">EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,</span>
<span id="LC22" class="line" lang="plaintext">PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR</span>
<span id="LC23" class="line" lang="plaintext">PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF</span>
<span id="LC24" class="line" lang="plaintext">LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING</span>
<span id="LC25" class="line" lang="plaintext">NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS</span>
<span id="LC26" class="line" lang="plaintext">SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. </span>
<span id="LC27" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1202:1-1202:10" dir="auto">
<a id="user-content-pyyaml" class="anchor" href="#pyyaml" aria-hidden="true"></a>PyYAML</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright (c) 2006 Kirill Simonov</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy of</span>
<span id="LC5" class="line" lang="plaintext">this software and associated documentation files (the "Software"), to deal in</span>
<span id="LC6" class="line" lang="plaintext">the Software without restriction, including without limitation the rights to</span>
<span id="LC7" class="line" lang="plaintext">use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies</span>
<span id="LC8" class="line" lang="plaintext">of the Software, and to permit persons to whom the Software is furnished to do</span>
<span id="LC9" class="line" lang="plaintext">so, subject to the following conditions:</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in all</span>
<span id="LC12" class="line" lang="plaintext">copies or substantial portions of the Software.</span>
<span id="LC13" class="line" lang="plaintext"></span>
<span id="LC14" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR</span>
<span id="LC15" class="line" lang="plaintext">IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,</span>
<span id="LC16" class="line" lang="plaintext">FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE</span>
<span id="LC17" class="line" lang="plaintext">AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER</span>
<span id="LC18" class="line" lang="plaintext">LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,</span>
<span id="LC19" class="line" lang="plaintext">OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE</span>
<span id="LC20" class="line" lang="plaintext">SOFTWARE.</span>
<span id="LC21" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1228:1-1228:12" dir="auto">
<a id="user-content-autobahn" class="anchor" href="#autobahn" aria-hidden="true"></a>Autobahn</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">The MIT License (MIT)</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Copyright (c) Crossbar.io Technologies GmbH</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy</span>
<span id="LC7" class="line" lang="plaintext">of this software and associated documentation files (the "Software"), to deal</span>
<span id="LC8" class="line" lang="plaintext">in the Software without restriction, including without limitation the rights</span>
<span id="LC9" class="line" lang="plaintext">to use, copy, modify, merge, publish, distribute, sublicense, and/or sell</span>
<span id="LC10" class="line" lang="plaintext">copies of the Software, and to permit persons to whom the Software is</span>
<span id="LC11" class="line" lang="plaintext">furnished to do so, subject to the following conditions:</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in</span>
<span id="LC14" class="line" lang="plaintext">all copies or substantial portions of the Software.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR</span>
<span id="LC17" class="line" lang="plaintext">IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,</span>
<span id="LC18" class="line" lang="plaintext">FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE</span>
<span id="LC19" class="line" lang="plaintext">AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER</span>
<span id="LC20" class="line" lang="plaintext">LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,</span>
<span id="LC21" class="line" lang="plaintext">OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN</span>
<span id="LC22" class="line" lang="plaintext">THE SOFTWARE.</span>
<span id="LC23" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1255:1-1255:11" dir="auto">
<a id="user-content-futures" class="anchor" href="#futures" aria-hidden="true"></a>futures</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">PYTHON SOFTWARE FOUNDATION LICENSE VERSION 2</span>
<span id="LC3" class="line" lang="plaintext">--------------------------------------------</span>
<span id="LC4" class="line" lang="plaintext"></span>
<span id="LC5" class="line" lang="plaintext">1. This LICENSE AGREEMENT is between the Python Software Foundation</span>
<span id="LC6" class="line" lang="plaintext">("PSF"), and the Individual or Organization ("Licensee") accessing and</span>
<span id="LC7" class="line" lang="plaintext">otherwise using this software ("Python") in source or binary form and</span>
<span id="LC8" class="line" lang="plaintext">its associated documentation.</span>
<span id="LC9" class="line" lang="plaintext"></span>
<span id="LC10" class="line" lang="plaintext">2. Subject to the terms and conditions of this License Agreement, PSF</span>
<span id="LC11" class="line" lang="plaintext">hereby grants Licensee a nonexclusive, royalty-free, world-wide</span>
<span id="LC12" class="line" lang="plaintext">license to reproduce, analyze, test, perform and/or display publicly,</span>
<span id="LC13" class="line" lang="plaintext">prepare derivative works, distribute, and otherwise use Python</span>
<span id="LC14" class="line" lang="plaintext">alone or in any derivative version, provided, however, that PSF\'s</span>
<span id="LC15" class="line" lang="plaintext">License Agreement and PSF\'s notice of copyright, i.e., "Copyright (c)</span>
<span id="LC16" class="line" lang="plaintext">2001, 2002, 2003, 2004, 2005, 2006 Python Software Foundation; All Rights</span>
<span id="LC17" class="line" lang="plaintext">Reserved" are retained in Python alone or in any derivative version </span>
<span id="LC18" class="line" lang="plaintext">prepared by Licensee.</span>
<span id="LC19" class="line" lang="plaintext"></span>
<span id="LC20" class="line" lang="plaintext">3. In the event Licensee prepares a derivative work that is based on</span>
<span id="LC21" class="line" lang="plaintext">or incorporates Python or any part thereof, and wants to make</span>
<span id="LC22" class="line" lang="plaintext">the derivative work available to others as provided herein, then</span>
<span id="LC23" class="line" lang="plaintext">Licensee hereby agrees to include in any such work a brief summary of</span>
<span id="LC24" class="line" lang="plaintext">the changes made to Python.</span>
<span id="LC25" class="line" lang="plaintext"></span>
<span id="LC26" class="line" lang="plaintext">4. PSF is making Python available to Licensee on an "AS IS"</span>
<span id="LC27" class="line" lang="plaintext">basis.  PSF MAKES NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR</span>
<span id="LC28" class="line" lang="plaintext">IMPLIED.  BY WAY OF EXAMPLE, BUT NOT LIMITATION, PSF MAKES NO AND</span>
<span id="LC29" class="line" lang="plaintext">DISCLAIMS ANY REPRESENTATION OR WARRANTY OF MERCHANTABILITY OR FITNESS</span>
<span id="LC30" class="line" lang="plaintext">FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF PYTHON WILL NOT</span>
<span id="LC31" class="line" lang="plaintext">INFRINGE ANY THIRD PARTY RIGHTS.</span>
<span id="LC32" class="line" lang="plaintext"></span>
<span id="LC33" class="line" lang="plaintext">5. PSF SHALL NOT BE LIABLE TO LICENSEE OR ANY OTHER USERS OF PYTHON</span>
<span id="LC34" class="line" lang="plaintext">FOR ANY INCIDENTAL, SPECIAL, OR CONSEQUENTIAL DAMAGES OR LOSS AS</span>
<span id="LC35" class="line" lang="plaintext">A RESULT OF MODIFYING, DISTRIBUTING, OR OTHERWISE USING PYTHON,</span>
<span id="LC36" class="line" lang="plaintext">OR ANY DERIVATIVE THEREOF, EVEN IF ADVISED OF THE POSSIBILITY THEREOF.</span>
<span id="LC37" class="line" lang="plaintext"></span>
<span id="LC38" class="line" lang="plaintext">6. This License Agreement will automatically terminate upon a material</span>
<span id="LC39" class="line" lang="plaintext">breach of its terms and conditions.</span>
<span id="LC40" class="line" lang="plaintext"></span>
<span id="LC41" class="line" lang="plaintext">7. Nothing in this License Agreement shall be deemed to create any</span>
<span id="LC42" class="line" lang="plaintext">relationship of agency, partnership, or joint venture between PSF and</span>
<span id="LC43" class="line" lang="plaintext">Licensee.  This License Agreement does not grant permission to use PSF</span>
<span id="LC44" class="line" lang="plaintext">trademarks or trade name in a trademark sense to endorse or promote</span>
<span id="LC45" class="line" lang="plaintext">products or services of Licensee, or any third party.</span>
<span id="LC46" class="line" lang="plaintext"></span>
<span id="LC47" class="line" lang="plaintext">8. By copying, installing or otherwise using Python, Licensee</span>
<span id="LC48" class="line" lang="plaintext">agrees to be bound by the terms and conditions of this License</span>
<span id="LC49" class="line" lang="plaintext">Agreement.</span>
<span id="LC50" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1310:1-1310:13" dir="auto">
<a id="user-content-pathtools" class="anchor" href="#pathtools" aria-hidden="true"></a>pathtools</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright (C) 2010 by Yesudeep Mangalapilly &lt;yesudeep@gmail.com&gt;</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">MIT License</span>
<span id="LC5" class="line" lang="plaintext">-----------</span>
<span id="LC6" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy</span>
<span id="LC7" class="line" lang="plaintext">of this software and associated documentation files (the "Software"), to deal</span>
<span id="LC8" class="line" lang="plaintext">in the Software without restriction, including without limitation the rights</span>
<span id="LC9" class="line" lang="plaintext">to use, copy, modify, merge, publish, distribute, sublicense, and/or sell</span>
<span id="LC10" class="line" lang="plaintext">copies of the Software, and to permit persons to whom the Software is</span>
<span id="LC11" class="line" lang="plaintext">furnished to do so, subject to the following conditions:</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in</span>
<span id="LC14" class="line" lang="plaintext">all copies or substantial portions of the Software.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR</span>
<span id="LC17" class="line" lang="plaintext">IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,</span>
<span id="LC18" class="line" lang="plaintext">FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE</span>
<span id="LC19" class="line" lang="plaintext">AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER</span>
<span id="LC20" class="line" lang="plaintext">LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,</span>
<span id="LC21" class="line" lang="plaintext">OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN</span>
<span id="LC22" class="line" lang="plaintext">THE SOFTWARE.</span>
<span id="LC23" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1338:1-1338:12" dir="auto">
<a id="user-content-protobuf" class="anchor" href="#protobuf" aria-hidden="true"></a>Protobuf</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">This license applies to all parts of Protocol Buffers except the following:</span>
<span id="LC3" class="line" lang="plaintext">  - Atomicops support for generic gcc, located in</span>
<span id="LC4" class="line" lang="plaintext">    src/google/protobuf/stubs/atomicops_internals_generic_gcc.h.</span>
<span id="LC5" class="line" lang="plaintext">    This file is copyrighted by Red Hat Inc.</span>
<span id="LC6" class="line" lang="plaintext">  - Atomicops support for AIX/POWER, located in</span>
<span id="LC7" class="line" lang="plaintext">    src/google/protobuf/stubs/atomicops_internals_power.h.</span>
<span id="LC8" class="line" lang="plaintext">    This file is copyrighted by Bloomberg Finance LP.</span>
<span id="LC9" class="line" lang="plaintext">Copyright 2014, Google Inc.  All rights reserved.</span>
<span id="LC10" class="line" lang="plaintext">Redistribution and use in source and binary forms, with or without</span>
<span id="LC11" class="line" lang="plaintext">modification, are permitted provided that the following conditions are</span>
<span id="LC12" class="line" lang="plaintext">met:</span>
<span id="LC13" class="line" lang="plaintext">    * Redistributions of source code must retain the above copyright</span>
<span id="LC14" class="line" lang="plaintext">notice, this list of conditions and the following disclaimer.</span>
<span id="LC15" class="line" lang="plaintext">    * Redistributions in binary form must reproduce the above</span>
<span id="LC16" class="line" lang="plaintext">copyright notice, this list of conditions and the following disclaimer</span>
<span id="LC17" class="line" lang="plaintext">in the documentation and/or other materials provided with the</span>
<span id="LC18" class="line" lang="plaintext">distribution.</span>
<span id="LC19" class="line" lang="plaintext">    * Neither the name of Google Inc. nor the names of its</span>
<span id="LC20" class="line" lang="plaintext">contributors may be used to endorse or promote products derived from</span>
<span id="LC21" class="line" lang="plaintext">this software without specific prior written permission.</span>
<span id="LC22" class="line" lang="plaintext"></span>
<span id="LC23" class="line" lang="plaintext">THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS</span>
<span id="LC24" class="line" lang="plaintext">"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT</span>
<span id="LC25" class="line" lang="plaintext">LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR</span>
<span id="LC26" class="line" lang="plaintext">A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT</span>
<span id="LC27" class="line" lang="plaintext">OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,</span>
<span id="LC28" class="line" lang="plaintext">SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT</span>
<span id="LC29" class="line" lang="plaintext">LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,</span>
<span id="LC30" class="line" lang="plaintext">DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY</span>
<span id="LC31" class="line" lang="plaintext">THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT</span>
<span id="LC32" class="line" lang="plaintext">(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE</span>
<span id="LC33" class="line" lang="plaintext">OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</span>
<span id="LC34" class="line" lang="plaintext"></span>
<span id="LC35" class="line" lang="plaintext">Code generated by the Protocol Buffer compiler is owned by the owner</span>
<span id="LC36" class="line" lang="plaintext">of the input file used when generating it.  This code is not</span>
<span id="LC37" class="line" lang="plaintext">standalone and requires a support library to be linked with it.  This</span>
<span id="LC38" class="line" lang="plaintext">support library is itself covered by the above license.</span>
<span id="LC39" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1382:1-1382:11" dir="auto">
<a id="user-content-scandir" class="anchor" href="#scandir" aria-hidden="true"></a>scandir</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">Copyright (c) 2012, Ben Hoyt</span>
<span id="LC3" class="line" lang="plaintext">All rights reserved.</span>
<span id="LC4" class="line" lang="plaintext"></span>
<span id="LC5" class="line" lang="plaintext">Redistribution and use in source and binary forms, with or without</span>
<span id="LC6" class="line" lang="plaintext">modification, are permitted provided that the following conditions are met:</span>
<span id="LC7" class="line" lang="plaintext"></span>
<span id="LC8" class="line" lang="plaintext">* Redistributions of source code must retain the above copyright notice, this</span>
<span id="LC9" class="line" lang="plaintext">list of conditions and the following disclaimer.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">* Redistributions in binary form must reproduce the above copyright notice,</span>
<span id="LC12" class="line" lang="plaintext">this list of conditions and the following disclaimer in the documentation</span>
<span id="LC13" class="line" lang="plaintext">and/or other materials provided with the distribution.</span>
<span id="LC14" class="line" lang="plaintext"></span>
<span id="LC15" class="line" lang="plaintext">* Neither the name of Ben Hoyt nor the names of its contributors may be used</span>
<span id="LC16" class="line" lang="plaintext">to endorse or promote products derived from this software without specific</span>
<span id="LC17" class="line" lang="plaintext">prior written permission.</span>
<span id="LC18" class="line" lang="plaintext"></span>
<span id="LC19" class="line" lang="plaintext">THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"</span>
<span id="LC20" class="line" lang="plaintext">AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE</span>
<span id="LC21" class="line" lang="plaintext">IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE</span>
<span id="LC22" class="line" lang="plaintext">DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE</span>
<span id="LC23" class="line" lang="plaintext">FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL</span>
<span id="LC24" class="line" lang="plaintext">DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR</span>
<span id="LC25" class="line" lang="plaintext">SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER</span>
<span id="LC26" class="line" lang="plaintext">CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,</span>
<span id="LC27" class="line" lang="plaintext">OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE</span>
<span id="LC28" class="line" lang="plaintext">OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</span>
<span id="LC29" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1416:1-1416:9" dir="auto">
<a id="user-content-txaio" class="anchor" href="#txaio" aria-hidden="true"></a>txaio</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">The MIT License (MIT)</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Copyright (c) Crossbar.io Technologies GmbH</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy</span>
<span id="LC7" class="line" lang="plaintext">of this software and associated documentation files (the "Software"), to deal</span>
<span id="LC8" class="line" lang="plaintext">in the Software without restriction, including without limitation the rights</span>
<span id="LC9" class="line" lang="plaintext">to use, copy, modify, merge, publish, distribute, sublicense, and/or sell</span>
<span id="LC10" class="line" lang="plaintext">copies of the Software, and to permit persons to whom the Software is</span>
<span id="LC11" class="line" lang="plaintext">furnished to do so, subject to the following conditions:</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in</span>
<span id="LC14" class="line" lang="plaintext">all copies or substantial portions of the Software.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR</span>
<span id="LC17" class="line" lang="plaintext">IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,</span>
<span id="LC18" class="line" lang="plaintext">FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE</span>
<span id="LC19" class="line" lang="plaintext">AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER</span>
<span id="LC20" class="line" lang="plaintext">LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,</span>
<span id="LC21" class="line" lang="plaintext">OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN</span>
<span id="LC22" class="line" lang="plaintext">THE SOFTWARE.</span>
<span id="LC23" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1444:1-1444:12" dir="auto">
<a id="user-content-watchdog" class="anchor" href="#watchdog" aria-hidden="true"></a>watchdog</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext"></span>
<span id="LC3" class="line" lang="plaintext">                                 Apache License</span>
<span id="LC4" class="line" lang="plaintext">                           Version 2.0, January 2004</span>
<span id="LC5" class="line" lang="plaintext">                        http://www.apache.org/licenses/</span>
<span id="LC6" class="line" lang="plaintext"></span>
<span id="LC7" class="line" lang="plaintext">   TERMS AND CONDITIONS FOR USE, REPRODUCTION, AND DISTRIBUTION</span>
<span id="LC8" class="line" lang="plaintext"></span>
<span id="LC9" class="line" lang="plaintext">   1. Definitions.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">      "License" shall mean the terms and conditions for use, reproduction,</span>
<span id="LC12" class="line" lang="plaintext">      and distribution as defined by Sections 1 through 9 of this document.</span>
<span id="LC13" class="line" lang="plaintext"></span>
<span id="LC14" class="line" lang="plaintext">      "Licensor" shall mean the copyright owner or entity authorized by</span>
<span id="LC15" class="line" lang="plaintext">      the copyright owner that is granting the License.</span>
<span id="LC16" class="line" lang="plaintext"></span>
<span id="LC17" class="line" lang="plaintext">      "Legal Entity" shall mean the union of the acting entity and all</span>
<span id="LC18" class="line" lang="plaintext">      other entities that control, are controlled by, or are under common</span>
<span id="LC19" class="line" lang="plaintext">      control with that entity. For the purposes of this definition,</span>
<span id="LC20" class="line" lang="plaintext">      "control" means (i) the power, direct or indirect, to cause the</span>
<span id="LC21" class="line" lang="plaintext">      direction or management of such entity, whether by contract or</span>
<span id="LC22" class="line" lang="plaintext">      otherwise, or (ii) ownership of fifty percent (50%) or more of the</span>
<span id="LC23" class="line" lang="plaintext">      outstanding shares, or (iii) beneficial ownership of such entity.</span>
<span id="LC24" class="line" lang="plaintext"></span>
<span id="LC25" class="line" lang="plaintext">      "You" (or "Your") shall mean an individual or Legal Entity</span>
<span id="LC26" class="line" lang="plaintext">      exercising permissions granted by this License.</span>
<span id="LC27" class="line" lang="plaintext"></span>
<span id="LC28" class="line" lang="plaintext">      "Source" form shall mean the preferred form for making modifications,</span>
<span id="LC29" class="line" lang="plaintext">      including but not limited to software source code, documentation</span>
<span id="LC30" class="line" lang="plaintext">      source, and configuration files.</span>
<span id="LC31" class="line" lang="plaintext"></span>
<span id="LC32" class="line" lang="plaintext">      "Object" form shall mean any form resulting from mechanical</span>
<span id="LC33" class="line" lang="plaintext">      transformation or translation of a Source form, including but</span>
<span id="LC34" class="line" lang="plaintext">      not limited to compiled object code, generated documentation,</span>
<span id="LC35" class="line" lang="plaintext">      and conversions to other media types.</span>
<span id="LC36" class="line" lang="plaintext"></span>
<span id="LC37" class="line" lang="plaintext">      "Work" shall mean the work of authorship, whether in Source or</span>
<span id="LC38" class="line" lang="plaintext">      Object form, made available under the License, as indicated by a</span>
<span id="LC39" class="line" lang="plaintext">      copyright notice that is included in or attached to the work</span>
<span id="LC40" class="line" lang="plaintext">      (an example is provided in the Appendix below).</span>
<span id="LC41" class="line" lang="plaintext"></span>
<span id="LC42" class="line" lang="plaintext">      "Derivative Works" shall mean any work, whether in Source or Object</span>
<span id="LC43" class="line" lang="plaintext">      form, that is based on (or derived from) the Work and for which the</span>
<span id="LC44" class="line" lang="plaintext">      editorial revisions, annotations, elaborations, or other modifications</span>
<span id="LC45" class="line" lang="plaintext">      represent, as a whole, an original work of authorship. For the purposes</span>
<span id="LC46" class="line" lang="plaintext">      of this License, Derivative Works shall not include works that remain</span>
<span id="LC47" class="line" lang="plaintext">      separable from, or merely link (or bind by name) to the interfaces of,</span>
<span id="LC48" class="line" lang="plaintext">      the Work and Derivative Works thereof.</span>
<span id="LC49" class="line" lang="plaintext"></span>
<span id="LC50" class="line" lang="plaintext">      "Contribution" shall mean any work of authorship, including</span>
<span id="LC51" class="line" lang="plaintext">      the original version of the Work and any modifications or additions</span>
<span id="LC52" class="line" lang="plaintext">      to that Work or Derivative Works thereof, that is intentionally</span>
<span id="LC53" class="line" lang="plaintext">      submitted to Licensor for inclusion in the Work by the copyright owner</span>
<span id="LC54" class="line" lang="plaintext">      or by an individual or Legal Entity authorized to submit on behalf of</span>
<span id="LC55" class="line" lang="plaintext">      the copyright owner. For the purposes of this definition, "submitted"</span>
<span id="LC56" class="line" lang="plaintext">      means any form of electronic, verbal, or written communication sent</span>
<span id="LC57" class="line" lang="plaintext">      to the Licensor or its representatives, including but not limited to</span>
<span id="LC58" class="line" lang="plaintext">      communication on electronic mailing lists, source code control systems,</span>
<span id="LC59" class="line" lang="plaintext">      and issue tracking systems that are managed by, or on behalf of, the</span>
<span id="LC60" class="line" lang="plaintext">      Licensor for the purpose of discussing and improving the Work, but</span>
<span id="LC61" class="line" lang="plaintext">      excluding communication that is conspicuously marked or otherwise</span>
<span id="LC62" class="line" lang="plaintext">      designated in writing by the copyright owner as "Not a Contribution."</span>
<span id="LC63" class="line" lang="plaintext"></span>
<span id="LC64" class="line" lang="plaintext">      "Contributor" shall mean Licensor and any individual or Legal Entity</span>
<span id="LC65" class="line" lang="plaintext">      on behalf of whom a Contribution has been received by Licensor and</span>
<span id="LC66" class="line" lang="plaintext">      subsequently incorporated within the Work.</span>
<span id="LC67" class="line" lang="plaintext"></span>
<span id="LC68" class="line" lang="plaintext">   2. Grant of Copyright License. Subject to the terms and conditions of</span>
<span id="LC69" class="line" lang="plaintext">      this License, each Contributor hereby grants to You a perpetual,</span>
<span id="LC70" class="line" lang="plaintext">      worldwide, non-exclusive, no-charge, royalty-free, irrevocable</span>
<span id="LC71" class="line" lang="plaintext">      copyright license to reproduce, prepare Derivative Works of,</span>
<span id="LC72" class="line" lang="plaintext">      publicly display, publicly perform, sublicense, and distribute the</span>
<span id="LC73" class="line" lang="plaintext">      Work and such Derivative Works in Source or Object form.</span>
<span id="LC74" class="line" lang="plaintext"></span>
<span id="LC75" class="line" lang="plaintext">   3. Grant of Patent License. Subject to the terms and conditions of</span>
<span id="LC76" class="line" lang="plaintext">      this License, each Contributor hereby grants to You a perpetual,</span>
<span id="LC77" class="line" lang="plaintext">      worldwide, non-exclusive, no-charge, royalty-free, irrevocable</span>
<span id="LC78" class="line" lang="plaintext">      (except as stated in this section) patent license to make, have made,</span>
<span id="LC79" class="line" lang="plaintext">      use, offer to sell, sell, import, and otherwise transfer the Work,</span>
<span id="LC80" class="line" lang="plaintext">      where such license applies only to those patent claims licensable</span>
<span id="LC81" class="line" lang="plaintext">      by such Contributor that are necessarily infringed by their</span>
<span id="LC82" class="line" lang="plaintext">      Contribution(s) alone or by combination of their Contribution(s)</span>
<span id="LC83" class="line" lang="plaintext">      with the Work to which such Contribution(s) was submitted. If You</span>
<span id="LC84" class="line" lang="plaintext">      institute patent litigation against any entity (including a</span>
<span id="LC85" class="line" lang="plaintext">      cross-claim or counterclaim in a lawsuit) alleging that the Work</span>
<span id="LC86" class="line" lang="plaintext">      or a Contribution incorporated within the Work constitutes direct</span>
<span id="LC87" class="line" lang="plaintext">      or contributory patent infringement, then any patent licenses</span>
<span id="LC88" class="line" lang="plaintext">      granted to You under this License for that Work shall terminate</span>
<span id="LC89" class="line" lang="plaintext">      as of the date such litigation is filed.</span>
<span id="LC90" class="line" lang="plaintext"></span>
<span id="LC91" class="line" lang="plaintext">   4. Redistribution. You may reproduce and distribute copies of the</span>
<span id="LC92" class="line" lang="plaintext">      Work or Derivative Works thereof in any medium, with or without</span>
<span id="LC93" class="line" lang="plaintext">      modifications, and in Source or Object form, provided that You</span>
<span id="LC94" class="line" lang="plaintext">      meet the following conditions:</span>
<span id="LC95" class="line" lang="plaintext"></span>
<span id="LC96" class="line" lang="plaintext">      (a) You must give any other recipients of the Work or</span>
<span id="LC97" class="line" lang="plaintext">          Derivative Works a copy of this License; and</span>
<span id="LC98" class="line" lang="plaintext"></span>
<span id="LC99" class="line" lang="plaintext">      (b) You must cause any modified files to carry prominent notices</span>
<span id="LC100" class="line" lang="plaintext">          stating that You changed the files; and</span>
<span id="LC101" class="line" lang="plaintext"></span>
<span id="LC102" class="line" lang="plaintext">      (c) You must retain, in the Source form of any Derivative Works</span>
<span id="LC103" class="line" lang="plaintext">          that You distribute, all copyright, patent, trademark, and</span>
<span id="LC104" class="line" lang="plaintext">          attribution notices from the Source form of the Work,</span>
<span id="LC105" class="line" lang="plaintext">          excluding those notices that do not pertain to any part of</span>
<span id="LC106" class="line" lang="plaintext">          the Derivative Works; and</span>
<span id="LC107" class="line" lang="plaintext"></span>
<span id="LC108" class="line" lang="plaintext">      (d) If the Work includes a "NOTICE" text file as part of its</span>
<span id="LC109" class="line" lang="plaintext">          distribution, then any Derivative Works that You distribute must</span>
<span id="LC110" class="line" lang="plaintext">          include a readable copy of the attribution notices contained</span>
<span id="LC111" class="line" lang="plaintext">          within such NOTICE file, excluding those notices that do not</span>
<span id="LC112" class="line" lang="plaintext">          pertain to any part of the Derivative Works, in at least one</span>
<span id="LC113" class="line" lang="plaintext">          of the following places: within a NOTICE text file distributed</span>
<span id="LC114" class="line" lang="plaintext">          as part of the Derivative Works; within the Source form or</span>
<span id="LC115" class="line" lang="plaintext">          documentation, if provided along with the Derivative Works; or,</span>
<span id="LC116" class="line" lang="plaintext">          within a display generated by the Derivative Works, if and</span>
<span id="LC117" class="line" lang="plaintext">          wherever such third-party notices normally appear. The contents</span>
<span id="LC118" class="line" lang="plaintext">          of the NOTICE file are for informational purposes only and</span>
<span id="LC119" class="line" lang="plaintext">          do not modify the License. You may add Your own attribution</span>
<span id="LC120" class="line" lang="plaintext">          notices within Derivative Works that You distribute, alongside</span>
<span id="LC121" class="line" lang="plaintext">          or as an addendum to the NOTICE text from the Work, provided</span>
<span id="LC122" class="line" lang="plaintext">          that such additional attribution notices cannot be construed</span>
<span id="LC123" class="line" lang="plaintext">          as modifying the License.</span>
<span id="LC124" class="line" lang="plaintext"></span>
<span id="LC125" class="line" lang="plaintext">      You may add Your own copyright statement to Your modifications and</span>
<span id="LC126" class="line" lang="plaintext">      may provide additional or different license terms and conditions</span>
<span id="LC127" class="line" lang="plaintext">      for use, reproduction, or distribution of Your modifications, or</span>
<span id="LC128" class="line" lang="plaintext">      for any such Derivative Works as a whole, provided Your use,</span>
<span id="LC129" class="line" lang="plaintext">      reproduction, and distribution of the Work otherwise complies with</span>
<span id="LC130" class="line" lang="plaintext">      the conditions stated in this License.</span>
<span id="LC131" class="line" lang="plaintext"></span>
<span id="LC132" class="line" lang="plaintext">   5. Submission of Contributions. Unless You explicitly state otherwise,</span>
<span id="LC133" class="line" lang="plaintext">      any Contribution intentionally submitted for inclusion in the Work</span>
<span id="LC134" class="line" lang="plaintext">      by You to the Licensor shall be under the terms and conditions of</span>
<span id="LC135" class="line" lang="plaintext">      this License, without any additional terms or conditions.</span>
<span id="LC136" class="line" lang="plaintext">      Notwithstanding the above, nothing herein shall supersede or modify</span>
<span id="LC137" class="line" lang="plaintext">      the terms of any separate license agreement you may have executed</span>
<span id="LC138" class="line" lang="plaintext">      with Licensor regarding such Contributions.</span>
<span id="LC139" class="line" lang="plaintext"></span>
<span id="LC140" class="line" lang="plaintext">   6. Trademarks. This License does not grant permission to use the trade</span>
<span id="LC141" class="line" lang="plaintext">      names, trademarks, service marks, or product names of the Licensor,</span>
<span id="LC142" class="line" lang="plaintext">      except as required for reasonable and customary use in describing the</span>
<span id="LC143" class="line" lang="plaintext">      origin of the Work and reproducing the content of the NOTICE file.</span>
<span id="LC144" class="line" lang="plaintext"></span>
<span id="LC145" class="line" lang="plaintext">   7. Disclaimer of Warranty. Unless required by applicable law or</span>
<span id="LC146" class="line" lang="plaintext">      agreed to in writing, Licensor provides the Work (and each</span>
<span id="LC147" class="line" lang="plaintext">      Contributor provides its Contributions) on an "AS IS" BASIS,</span>
<span id="LC148" class="line" lang="plaintext">      WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or</span>
<span id="LC149" class="line" lang="plaintext">      implied, including, without limitation, any warranties or conditions</span>
<span id="LC150" class="line" lang="plaintext">      of TITLE, NON-INFRINGEMENT, MERCHANTABILITY, or FITNESS FOR A</span>
<span id="LC151" class="line" lang="plaintext">      PARTICULAR PURPOSE. You are solely responsible for determining the</span>
<span id="LC152" class="line" lang="plaintext">      appropriateness of using or redistributing the Work and assume any</span>
<span id="LC153" class="line" lang="plaintext">      risks associated with Your exercise of permissions under this License.</span>
<span id="LC154" class="line" lang="plaintext"></span>
<span id="LC155" class="line" lang="plaintext">   8. Limitation of Liability. In no event and under no legal theory,</span>
<span id="LC156" class="line" lang="plaintext">      whether in tort (including negligence), contract, or otherwise,</span>
<span id="LC157" class="line" lang="plaintext">      unless required by applicable law (such as deliberate and grossly</span>
<span id="LC158" class="line" lang="plaintext">      negligent acts) or agreed to in writing, shall any Contributor be</span>
<span id="LC159" class="line" lang="plaintext">      liable to You for damages, including any direct, indirect, special,</span>
<span id="LC160" class="line" lang="plaintext">      incidental, or consequential damages of any character arising as a</span>
<span id="LC161" class="line" lang="plaintext">      result of this License or out of the use or inability to use the</span>
<span id="LC162" class="line" lang="plaintext">      Work (including but not limited to damages for loss of goodwill,</span>
<span id="LC163" class="line" lang="plaintext">      work stoppage, computer failure or malfunction, or any and all</span>
<span id="LC164" class="line" lang="plaintext">      other commercial damages or losses), even if such Contributor</span>
<span id="LC165" class="line" lang="plaintext">      has been advised of the possibility of such damages.</span>
<span id="LC166" class="line" lang="plaintext"></span>
<span id="LC167" class="line" lang="plaintext">   9. Accepting Warranty or Additional Liability. While redistributing</span>
<span id="LC168" class="line" lang="plaintext">      the Work or Derivative Works thereof, You may choose to offer,</span>
<span id="LC169" class="line" lang="plaintext">      and charge a fee for, acceptance of support, warranty, indemnity,</span>
<span id="LC170" class="line" lang="plaintext">      or other liability obligations and/or rights consistent with this</span>
<span id="LC171" class="line" lang="plaintext">      License. However, in accepting such obligations, You may act only</span>
<span id="LC172" class="line" lang="plaintext">      on Your own behalf and on Your sole responsibility, not on behalf</span>
<span id="LC173" class="line" lang="plaintext">      of any other Contributor, and only if You agree to indemnify,</span>
<span id="LC174" class="line" lang="plaintext">      defend, and hold each Contributor harmless for any liability</span>
<span id="LC175" class="line" lang="plaintext">      incurred by, or claims asserted against, such Contributor by reason</span>
<span id="LC176" class="line" lang="plaintext">      of your accepting any such warranty or additional liability.</span>
<span id="LC177" class="line" lang="plaintext"></span>
<span id="LC178" class="line" lang="plaintext">   END OF TERMS AND CONDITIONS</span>
<span id="LC179" class="line" lang="plaintext"></span>
<span id="LC180" class="line" lang="plaintext">   APPENDIX: How to apply the Apache License to your work.</span>
<span id="LC181" class="line" lang="plaintext"></span>
<span id="LC182" class="line" lang="plaintext">      To apply the Apache License to your work, attach the following</span>
<span id="LC183" class="line" lang="plaintext">      boilerplate notice, with the fields enclosed by brackets "[]"</span>
<span id="LC184" class="line" lang="plaintext">      replaced with your own identifying information. (Don\'t include</span>
<span id="LC185" class="line" lang="plaintext">      the brackets!)  The text should be enclosed in the appropriate</span>
<span id="LC186" class="line" lang="plaintext">      comment syntax for the file format. We also recommend that a</span>
<span id="LC187" class="line" lang="plaintext">      file or class name and description of purpose be included on the</span>
<span id="LC188" class="line" lang="plaintext">      same "printed page" as the copyright notice for easier</span>
<span id="LC189" class="line" lang="plaintext">      identification within third-party archives.</span>
<span id="LC190" class="line" lang="plaintext"></span>
<span id="LC191" class="line" lang="plaintext">   Copyright [yyyy] [name of copyright owner]</span>
<span id="LC192" class="line" lang="plaintext"></span>
<span id="LC193" class="line" lang="plaintext">   Licensed under the Apache License, Version 2.0 (the "License");</span>
<span id="LC194" class="line" lang="plaintext">   you may not use this file except in compliance with the License.</span>
<span id="LC195" class="line" lang="plaintext">   You may obtain a copy of the License at</span>
<span id="LC196" class="line" lang="plaintext"></span>
<span id="LC197" class="line" lang="plaintext">       http://www.apache.org/licenses/LICENSE-2.0</span>
<span id="LC198" class="line" lang="plaintext"></span>
<span id="LC199" class="line" lang="plaintext">   Unless required by applicable law or agreed to in writing, software</span>
<span id="LC200" class="line" lang="plaintext">   distributed under the License is distributed on an "AS IS" BASIS,</span>
<span id="LC201" class="line" lang="plaintext">   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.</span>
<span id="LC202" class="line" lang="plaintext">   See the License for the specific language governing permissions and</span>
<span id="LC203" class="line" lang="plaintext">   limitations under the License.</span>
<span id="LC204" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1653:1-1653:9" dir="auto">
<a id="user-content-fysom" class="anchor" href="#fysom" aria-hidden="true"></a>fysom</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">License</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">MIT licensed. All credits go to Jake Gordon for the original javascript implementation and to Mansour Behabadi for the python port.</span>
<span id="LC5" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1663:1-1663:11" dir="auto">
<a id="user-content-alembic" class="anchor" href="#alembic" aria-hidden="true"></a>alembic</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext"></span>
<span id="LC2" class="line" lang="plaintext">This is the MIT license: http://www.opensource.org/licenses/mit-license.php</span>
<span id="LC3" class="line" lang="plaintext"></span>
<span id="LC4" class="line" lang="plaintext">Copyright (C) 2009-2017 by Michael Bayer.</span>
<span id="LC5" class="line" lang="plaintext">Alembic is a trademark of Michael Bayer.</span>
<span id="LC6" class="line" lang="plaintext"></span>
<span id="LC7" class="line" lang="plaintext">Permission is hereby granted, free of charge, to any person obtaining a copy of this</span>
<span id="LC8" class="line" lang="plaintext">software and associated documentation files (the "Software"), to deal in the Software</span>
<span id="LC9" class="line" lang="plaintext">without restriction, including without limitation the rights to use, copy, modify, merge,</span>
<span id="LC10" class="line" lang="plaintext">publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons</span>
<span id="LC11" class="line" lang="plaintext">to whom the Software is furnished to do so, subject to the following conditions:</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">The above copyright notice and this permission notice shall be included in all copies or</span>
<span id="LC14" class="line" lang="plaintext">substantial portions of the Software.</span>
<span id="LC15" class="line" lang="plaintext"></span>
<span id="LC16" class="line" lang="plaintext">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,</span>
<span id="LC17" class="line" lang="plaintext">INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR</span>
<span id="LC18" class="line" lang="plaintext">PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE</span>
<span id="LC19" class="line" lang="plaintext">FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR</span>
<span id="LC20" class="line" lang="plaintext">OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER</span>
<span id="LC21" class="line" lang="plaintext">DEALINGS IN THE SOFTWARE.</span>
<span id="LC22" class="line" lang="plaintext"></span></code></pre>
<h3 data-sourcepos="1690:1-1690:10" dir="auto">
<a id="user-content-python" class="anchor" href="#python" aria-hidden="true"></a>python</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext">PSF LICENSE AGREEMENT FOR PYTHON 3.7.3</span>
<span id="LC2" class="line" lang="plaintext">1. This LICENSE AGREEMENT is between the Python Software Foundation ("PSF"), and</span>
<span id="LC3" class="line" lang="plaintext">   the Individual or Organization ("Licensee") accessing and otherwise using Python</span>
<span id="LC4" class="line" lang="plaintext">   3.7.3 software in source or binary form and its associated documentation.</span>
<span id="LC5" class="line" lang="plaintext"></span>
<span id="LC6" class="line" lang="plaintext">2. Subject to the terms and conditions of this License Agreement, PSF hereby</span>
<span id="LC7" class="line" lang="plaintext">   grants Licensee a nonexclusive, royalty-free, world-wide license to reproduce,</span>
<span id="LC8" class="line" lang="plaintext">   analyze, test, perform and/or display publicly, prepare derivative works,</span>
<span id="LC9" class="line" lang="plaintext">   distribute, and otherwise use Python 3.7.3 alone or in any derivative</span>
<span id="LC10" class="line" lang="plaintext">   version, provided, however, that PSF\'s License Agreement and PSF\'s notice of</span>
<span id="LC11" class="line" lang="plaintext">   copyright, i.e., "Copyright © 2001-2019 Python Software Foundation; All Rights</span>
<span id="LC12" class="line" lang="plaintext">   Reserved" are retained in Python 3.7.3 alone or in any derivative version</span>
<span id="LC13" class="line" lang="plaintext">   prepared by Licensee.</span>
<span id="LC14" class="line" lang="plaintext"></span>
<span id="LC15" class="line" lang="plaintext">3. In the event Licensee prepares a derivative work that is based on or</span>
<span id="LC16" class="line" lang="plaintext">   incorporates Python 3.7.3 or any part thereof, and wants to make the</span>
<span id="LC17" class="line" lang="plaintext">   derivative work available to others as provided herein, then Licensee hereby</span>
<span id="LC18" class="line" lang="plaintext">   agrees to include in any such work a brief summary of the changes made to Python</span>
<span id="LC19" class="line" lang="plaintext">   3.7.3.</span>
<span id="LC20" class="line" lang="plaintext"></span>
<span id="LC21" class="line" lang="plaintext">4. PSF is making Python 3.7.3 available to Licensee on an "AS IS" basis.</span>
<span id="LC22" class="line" lang="plaintext">   PSF MAKES NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED.  BY WAY OF</span>
<span id="LC23" class="line" lang="plaintext">   EXAMPLE, BUT NOT LIMITATION, PSF MAKES NO AND DISCLAIMS ANY REPRESENTATION OR</span>
<span id="LC24" class="line" lang="plaintext">   WARRANTY OF MERCHANTABILITY OR FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE</span>
<span id="LC25" class="line" lang="plaintext">   USE OF PYTHON 3.7.3 WILL NOT INFRINGE ANY THIRD PARTY RIGHTS.</span>
<span id="LC26" class="line" lang="plaintext"></span>
<span id="LC27" class="line" lang="plaintext">5. PSF SHALL NOT BE LIABLE TO LICENSEE OR ANY OTHER USERS OF PYTHON 3.7.3</span>
<span id="LC28" class="line" lang="plaintext">   FOR ANY INCIDENTAL, SPECIAL, OR CONSEQUENTIAL DAMAGES OR LOSS AS A RESULT OF</span>
<span id="LC29" class="line" lang="plaintext">   MODIFYING, DISTRIBUTING, OR OTHERWISE USING PYTHON 3.7.3, OR ANY DERIVATIVE</span>
<span id="LC30" class="line" lang="plaintext">   THEREOF, EVEN IF ADVISED OF THE POSSIBILITY THEREOF.</span>
<span id="LC31" class="line" lang="plaintext"></span>
<span id="LC32" class="line" lang="plaintext">6. This License Agreement will automatically terminate upon a material breach of</span>
<span id="LC33" class="line" lang="plaintext">   its terms and conditions.</span>
<span id="LC34" class="line" lang="plaintext"></span>
<span id="LC35" class="line" lang="plaintext">7. Nothing in this License Agreement shall be deemed to create any relationship</span>
<span id="LC36" class="line" lang="plaintext">   of agency, partnership, or joint venture between PSF and Licensee.  This License</span>
<span id="LC37" class="line" lang="plaintext">   Agreement does not grant permission to use PSF trademarks or trade name in a</span>
<span id="LC38" class="line" lang="plaintext">   trademark sense to endorse or promote products or services of Licensee, or any</span>
<span id="LC39" class="line" lang="plaintext">   third party.</span>
<span id="LC40" class="line" lang="plaintext"></span>
<span id="LC41" class="line" lang="plaintext">8. By copying, installing or otherwise using Python 3.7.3, Licensee agrees</span>
<span id="LC42" class="line" lang="plaintext">   to be bound by the terms and conditions of this License Agreement.</span></code></pre>
<h3 data-sourcepos="1737:1-1737:12" dir="auto">
<a id="user-content-elfinder" class="anchor" href="#elfinder" aria-hidden="true"></a>elFinder</h3>
<pre class="code highlight js-syntax-highlight plaintext white" v-pre="true" lang="plaintext"><code><span id="LC1" class="line" lang="plaintext">elFinder is issued under a 3-clauses BSD license.</span>
<span id="LC2" class="line" lang="plaintext"></span>
<span id="LC3" class="line" lang="plaintext">Copyright (c) 2009-2018, Studio 42 All rights reserved.</span>
<span id="LC4" class="line" lang="plaintext"></span>
<span id="LC5" class="line" lang="plaintext">Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:</span>
<span id="LC6" class="line" lang="plaintext"></span>
<span id="LC7" class="line" lang="plaintext">1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.</span>
<span id="LC8" class="line" lang="plaintext"></span>
<span id="LC9" class="line" lang="plaintext">2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.</span>
<span id="LC10" class="line" lang="plaintext"></span>
<span id="LC11" class="line" lang="plaintext">3. Neither the name of the Studio 42 Ltd. nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.</span>
<span id="LC12" class="line" lang="plaintext"></span>
<span id="LC13" class="line" lang="plaintext">THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL "STUDIO 42" OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</span></code></pre>
</div>
    ',
];