��    (      \  5   �      p  �   q  !         %  #   F  E   j     �  �   �  "   �     �  .   �          "     <    P    i  z   r     �     	     	     +	  "  <	     _
  +   v
  4   �
     �
  �   �
  �   �  �   E  -   (     V     r  �   �     "     <     L    h    |  �   �  �     �  �  �   ]  2     )   Q  ,   {  O   �  )   �    "  &   /     V  6   u     �     �     �  8  �  0  6  �   g  3   '     [     z     �  c  �  3     0   G  M   x  '   �  �   �  �   �    �  >   �           "     5      <!     V!  (   k!  �   �!  ,  �"  �   �#  �   ^$           	                                       
   '   %              "         (                         &       #                                  $                    !           %sClick here%s to mount Gallery on your desktop with a WebDAV client.  Documentation on mounting Gallery with WebDAV is in the %sGallery Codex%s. 'Connect to WebDAV' rule disabled 'OPTIONS Requests' rule disabled Alternative URL missing DAV headers Alternatively, you can enter the following URL in your WebDAV client: Bad URL rewrite configuration Because OPTIONS responses are missing DAV headers, we try to fall back on an alternative URL, but alternative URL responses are also missing DAV headers.  Troubleshooting documentation is in the %sGallery Codex%s. Configuration checked successfully Connect to WebDAV Give davmount resources the correct extension. HTTP auth module disabled HTTP auth plugin disabled Missing DAV headers Most WebDAV clients will fail to connect because the URL rewrite module is disabled.  You should activate the URL rewrite module in the %sSite Admin Plugins option%s and choose either Apache mod_rewrite or ISAPI_Rewrite.  Troubleshooting documentation is in the %sGallery Codex%s. Most WebDAV clients will fail to connect because the URL rewrite rule to generate short WebDAV URLs is disabled.  You should activate the 'Connect to WebDAV' rule in the %sSite Admin URL Rewrite option%s.  Troubleshooting documentation is in the %sGallery Codex%s. Most WebDAV clients will successfully connect.  Documentation on mounting Gallery with WebDAV is in the %sGallery Codex%s. Mount Gallery on your desktop. Mount WebDAV Mount with WebDAV OPTIONS Requests PHP PathInfo rewrite doesn't support the rule to fall back on an alternative URL.  You should uninstall and reinstall the URL rewrite module in the %sSite Admin Plugins option%s and choose either Apache mod_rewrite or ISAPI_Rewrite.  Troubleshooting information is in the %sGallery Codex%s. PHP has no XML support Path to an item (eg. /album/image.jpg.html) Redirect OPTIONS requests so we can set DAV headers. Remote Interfaces Some WebDAV clients, e.g. Mac OS X WebDAVFS, will fail to connect and automated checks failed to find a cause.  Troubleshooting documentation is in the %sGallery Codex%s. Some WebDAV clients, e.g. Mac OS X WebDAVFS, will fail to connect because OPTIONS responses are missing DAV headers.  Troubleshooting documentation is in the %sGallery Codex%s. The URL rewrite rule to fall back on an alternative URL is disabled.  You should activate the WebDAV 'OPTIONS Requests' rule in the %sSite Admin URL Rewrite option%s.  Troubleshooting documentation is in the %sGallery Codex%s. The URL to connect to Gallery with WebDAV is: URL rewrite module disabled Unknown Cause Use short URL because most WebDAV clients don't support query strings.  The Windows WebDAV client requires that you don't add a slash before the %path% variable. WebDAV Mount Instructions WebDAV Settings WebDAV requests not handled You can connect with WebDAV anonymously, but you can't do anything which requires you to login because neither HTTP authentication nor server authentication are enabled in the HTTP auth module.  You should activate HTTP authentication in the settings of the HTTP auth module. You can connect with WebDAV anonymously, but you can't do anything which requires you to login because the HTTP auth module is disabled.  You should activate the HTTP auth module in the %sSite Admin Plugins option%s.  Troubleshooting documentation is in the %sGallery Codex%s. You can't connect with WebDAV because PHP has no XML support on this server.  Troubleshooting documentation is in the %sGallery Codex%s. You can't connect with WebDAV because this server doesn't pass WebDAV requests to Gallery.  Troubleshooting documentation is in the %sGallery Codex%s. Project-Id-Version: Gallery: WebDAV 1.0.8
Report-Msgid-Bugs-To: gallery-translations@lists.sourceforge.net
POT-Creation-Date: 2006-11-16 01:37+0100
PO-Revision-Date: 2007-03-03 22:11+0100
Last-Translator: Andy Staudacher <ast@gmx.ch>
Language-Team: German <gallery-devel@lists.sourceforge.net>
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=(n != 1);
 %sKlicken Sie hier%s um Gallery als Netzlaufwerk auf Ihrem Computer zu öffnen. Eine ausführliche Anleitung um Gallery mit einem WebDAV Programm zu öffnen befindet sich im %sGallery Codex%s. 'Als Netzwerklaufwerk verbinden' Regel ist inaktiv 'HTTP OPTIONS Anfragen' Regel ist inaktiv Alternative URL liefert keine DAV Kopfzeilen Alternativ können Sie auch die folgende URL in Ihrem WebDAV Programm eingeben: Inkorrekte Konfiguration von 'Kurze URLs' Da Antworten auf HTTP OPTIONS Anfragen keine DAV Kopfzeilen enthalten, versucht Gallery auf eine alternative URL zurückzufallen. Aber Antworten auf die alternative URL haben auch keine DAV Kopfzeilen. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Konfiguration erforlgreich überprüft Als Netzwerklaufwerk verbinden Geben Sie davmount Ressourcen die richtige Dateiendung HTTP auth Modul ist inaktiv HTTP auth Plugin ist inaktiv Fehlende DAV Kopfzeilen Die meisten WebDAV Programme können nicht mit Ihrer Gallery verbinden, da das 'Kurze URLs' Modul inaktiv ist. Bitte aktivieren Sie das 'Kurze URLs' Modul auf der %sPlugin Management Seite%s und wählen Sie Apache mod_rewrite oder ISAPI_Rewrite. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Die meisten WebDAV Programme können nicht mit Ihrer Gallery verbinden, da die 'Kurze URLs' Regel für WebDAV URLs inaktiv ist. Bitte aktivieren Sie die 'Als Netzlaufwerk verbinden' Regel in den %sEinstellungen des 'Kurze URLs' Moduls%s. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Die meisten WebDAV Programme können erfolgreich mit dieser Gallery verbinden. Eine ausführliche Anleitung um Gallery mit einem WebDAV Programm zu öffnen befindet sich im %sGallery Codex%s. Gallery von Ihrem Computer als Netzlaufwerk öffnen Als Netzwerklaufwerk verbinden Als Netzwerklaufwerk verbinden HTTP OPTIONS Anfragen 'Kurze URLs' mit PHP PathInfo kann die alternative WebDAV URL Regel nicht unterstützen. Bitte deinstallieren Sie das 'Kurze URLs' Modul auf der %sPlugin Management Seite%s und wählen Sie beim installieren des Moduls entweder Apache mod_rewrite oder ISAPI_Rewrite, jedoch nicht PathInfo. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Ihrer PHP Installation fehlt die XML Unterstützung Pfad eines Elementes (z.B. /album/bild.jpg.html) HTTP OPTIONs Anfragen umleiten, damit Gallery die DAV Kopfzeilen setzen kann. Anwendungs- und Programm-Schnittstellen Einige WebDAV Programme (z.B. Mac OS X WebDAVFS) funktionieren nicht mit Ihrer Gallery und die Ursache für dieses Fehlerverhalten konnte nicht festgestellt werden. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Einige WebDAV Programme (z.B. Mac OS X WebDAVFS) können nicht mit Ihrer Gallery verbinden, da Antworten auf HTTP OPTIONS Anfragen keine DAV Kopfzeilen enthalten. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Die 'Kurze URLs' Regel um auf eine alternative URL zurückzufallen ist inaktiv.  Bitte aktivieren Sie die WebDAV 'HTTP OPTIONS Anfragen' Regel in den %sKurze URLs Site-Administration's Optionen%s. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Die URL um mit Gallery als WebDAV Netzlaufwerk zu öffnen ist: 'Kurze URLs' Modul ist inaktiv Unbekannte Ursache Nutzen Sie kurze URLs, da die meisten WebDAV Programme das Format der dynamischen URLs in Gallery nicht verarbeiten können.  Falls Sie den Windows WebDAV-Client nutzen wollen, müssen Sie sicherstellen, dass vor dem '%path%' Platzhalter keinSchrägstrich liegt. WebDAV Benutzer Anleitung WebDAV Einstellungen WebDAV Anfragen werden nicht verarbeitet Sie können als Gast-Benutzer WebDAV nutzen, aber Sie können keine Aktionen als registrierter Benutzer durchführen, da keine 'HTTP auth' Plugins aktiv sind. Bitte aktivieren Sie die HTTP Authentifizierung in den Einstellungen des HTTP auth Moduls. Sie können als Gast-Benutzer WebDAV nutzen, aber Sie können keine Aktionen als registrierter Benutzer durchführen, da das 'HTTP auth' Modul inaktiv ist. Bitte aktivieren Sie das HTTP auth Modul auf der %sPlugin Management Seite%s. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Ihre Gallery unterstützt WebDAV nicht, da Ihre PHP Installation XML nicht interpretieren kann. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. Ihre Gallery unterstützt WebDAV nicht, da Ihr Webserver die WebDAV Anfragen nicht an Gallery weiterleitet. Hinweise zur Fehlerbehandlung sind im %sGallery Codex%s zu finden. 