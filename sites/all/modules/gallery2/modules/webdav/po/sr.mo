��    )      d  ;   �      �  �   �  !   +      M  #   n  E   �     �  �   �  "   �     �  .        0     J     d    x    �  z   �     	     4	     A	     S	  "  d	     �
  +   �
  4   �
     �
  �     �   �  �   m  -   P     ~     �  �   �     J     d  
   t         �    �  �   �  �   M  �  �  �   p  /     &   G  .   n  E   �  "   �  �         �       3     )   R  )   |     �    �    �  �   �  $   {     �     �     �    �     �  /     G   7       �   �  �   X  �   	  3   �  $   &     K  �   Z     �          .     <  8  Z    �   �   �!  �   "               	                                            !              #                              
                             $   '   %                           "       &              (         )    %sClick here%s to mount Gallery on your desktop with a WebDAV client.  Documentation on mounting Gallery with WebDAV is in the %sGallery Codex%s. 'Connect to WebDAV' rule disabled 'OPTIONS Requests' rule disabled Alternative URL missing DAV headers Alternatively, you can enter the following URL in your WebDAV client: Bad URL rewrite configuration Because OPTIONS responses are missing DAV headers, we try to fall back on an alternative URL, but alternative URL responses are also missing DAV headers.  Troubleshooting documentation is in the %sGallery Codex%s. Configuration checked successfully Connect to WebDAV Give davmount resources the correct extension. HTTP auth module disabled HTTP auth plugin disabled Missing DAV headers Most WebDAV clients will fail to connect because the URL rewrite module is disabled.  You should activate the URL rewrite module in the %sSite Admin Plugins option%s and choose either Apache mod_rewrite or ISAPI_Rewrite.  Troubleshooting documentation is in the %sGallery Codex%s. Most WebDAV clients will fail to connect because the URL rewrite rule to generate short WebDAV URLs is disabled.  You should activate the 'Connect to WebDAV' rule in the %sSite Admin URL Rewrite option%s.  Troubleshooting documentation is in the %sGallery Codex%s. Most WebDAV clients will successfully connect.  Documentation on mounting Gallery with WebDAV is in the %sGallery Codex%s. Mount Gallery on your desktop. Mount WebDAV Mount with WebDAV OPTIONS Requests PHP PathInfo rewrite doesn't support the rule to fall back on an alternative URL.  You should uninstall and reinstall the URL rewrite module in the %sSite Admin Plugins option%s and choose either Apache mod_rewrite or ISAPI_Rewrite.  Troubleshooting information is in the %sGallery Codex%s. PHP has no XML support Path to an item (eg. /album/image.jpg.html) Redirect OPTIONS requests so we can set DAV headers. Remote Interfaces Some WebDAV clients, e.g. Mac OS X WebDAVFS, will fail to connect and automated checks failed to find a cause.  Troubleshooting documentation is in the %sGallery Codex%s. Some WebDAV clients, e.g. Mac OS X WebDAVFS, will fail to connect because OPTIONS responses are missing DAV headers.  Troubleshooting documentation is in the %sGallery Codex%s. The URL rewrite rule to fall back on an alternative URL is disabled.  You should activate the WebDAV 'OPTIONS Requests' rule in the %sSite Admin URL Rewrite option%s.  Troubleshooting documentation is in the %sGallery Codex%s. The URL to connect to Gallery with WebDAV is: URL rewrite module disabled Unknown Cause Use short URL because most WebDAV clients don't support query strings.  The Windows WebDAV client requires that you don't add a slash before the %path% variable. WebDAV Mount Instructions WebDAV Settings WebDAV URL WebDAV requests not handled You can connect with WebDAV anonymously, but you can't do anything which requires you to login because neither HTTP authentication nor server authentication are enabled in the HTTP auth module.  You should activate HTTP authentication in the settings of the HTTP auth module. You can connect with WebDAV anonymously, but you can't do anything which requires you to login because the HTTP auth module is disabled.  You should activate the HTTP auth module in the %sSite Admin Plugins option%s.  Troubleshooting documentation is in the %sGallery Codex%s. You can't connect with WebDAV because PHP has no XML support on this server.  Troubleshooting documentation is in the %sGallery Codex%s. You can't connect with WebDAV because this server doesn't pass WebDAV requests to Gallery.  Troubleshooting documentation is in the %sGallery Codex%s. Project-Id-Version: Gallery: WebDAV 1.0.8
Report-Msgid-Bugs-To: gallery-translations@lists.sourceforge.net
POT-Creation-Date: 2007-03-07 17:11+0000
PO-Revision-Date: 2007-04-16 13:21+0100
Last-Translator: Jozef Selesi <selesi@gmail.com>
Language-Team: Serbian <gallery-translations@lists.sourceforge.net>
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
 %sKliknite ovde%s da montirate Galeriju na vaš desktop pomoću WebDAV klijenta. Dokumentacija o montiranju Galerije preko WebDAV se nalazi u %sGalerijinom kodeksu%s. Pravilo 'Povezivanje preko WebDAV' je neaktivno Pravilo 'OPTIONS zahtevi' je neaktivno Alternativnim adresama nedostaju DAV zaglavlja Kao alternativa, možete uneti sledeću adresu u vaš WebDAV klijent: Loše konfiguracija prepisa adresa Poštno OPTIONS odgovorima nedostaju DAV zaglavlja, pokušaćemo da koristimo alternativnu adresu, ali i odgovorima sa alternativnih adresa nedostaju DAV zaglavlja. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Konfiguracija uspešno proverena Povezivanje preko WebDAV Dodeli davmount resursima odgovarajuću ekstenziju. Modul za HTTP ovlašćenje je deaktiviran Modul za HTTP ovlašćenje je deaktiviran Nedostaju DAV zaglavlja Većina WebDAV klijenata neće uspeti da se poveže, jer je modul za prepis adresa isključen. Trebalo bi da aktivirate modul za prepis adresa u %sadministraciji modula%s i izaberete Apache mod_rewrite ili ISAPI_Rewrite. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Većina WebDAV klijenata neće uspeti da se poveže, jer je neaktivno pravilo za prepis adresa koje generiše kratke WebDAV adrese. Trebalo bi da aktivirate pravilo 'Povezivanje preko WebDAV' u %sadministraciji prepisa adresa%s. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Većina WebDAV klijenata će se uspešno povezati. Dokumentacija o montiranju Galerije preko WebDAV se nalazi u %sGalerijinom kodeksu%s. Montiranje Galerije na vaš desktop. Montiraj WebDAV Montiranje preko WebDAV OPTIONS zahtevi PHP PathInfo prepis ne podržava pravilo povratka na alternativne adrese. Trebalo bi da deinstalirate i ponovo instalirate modul za prepis adresa u %sadministraciji modula%s i izaberete Apache mod_rewrite ili ISAPI_Rewrite. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. PHP nema XML podršku Putanja do objekta (npr. /album/slika.jpg.html) Preusmeravanje OPTIONS zahteva kako bismo mogli podesiti DAV zaglavlja. Daljinski interfejsi Neki WebDAV klijenti, npr. MacOS X WebDAVFS, neće uspeti da se povežu, ali automatizovane provere nisu uspele da otkriju uzrok problema. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Neki WebDAV klijenti, npr. MacOS X WebDAVFS, neće uspeti da se povežu jer OPTIONS odgovorima nedostaju DAV zaglavlja. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Pravilo prepisa adresa za povratak na alternativne adrese je isključeno. Trebalo bi da aktivirate WebDAV pravilo 'OPTIONS zahtevi' u %sadministraciji modula prepisa adresa%s. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Adresa za povezivanje sa Galerijom preko WebDAV je: Modul za prepis adresa je isključen Nepoznat uzrok Koristite kratke adrese, pošto većina WebDAV klijenata ne podržava upitne nizove. Windows WebDAV klijent zahteva da ne dodajete kosu crtu pre %path% promenljive. Uputstvo za WebDAV montiranje WebDAV parametri WebDAV adresa WebDAV zahtevi se ne obrđuju Možete se anonimno povezati preko WebDAV, ali ne možete da radite bilo šta što zahteva prijavu, jer ni HTTP ovlašćenje, ni serversko ovlašćenje nisu uključeni u modulu HTTP ovlašćenje. Treba da aktivirate ovaj modul u %sadministraciji modula%s. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Možete se anonimno povezati preko WebDAV, ali ne možete da radite bilo šta što zahteva prijavu, jer je modul HTTP ovlašćenje neaktivan. Treba da aktivirate ovaj modul u %sadministraciji modula%s. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Ne možete se povezati preko WebDAV, jer PHP ne podržava XML na ovom serveru. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. Ne možete se povezati preko WebDAV, jer ovaj server ne prenosi WebDAV zahteve Galeriji. Dodatne informacije se nalaze u %sGalerijinom kodeksu%s. 