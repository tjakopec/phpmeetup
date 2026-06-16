<?php

namespace App\DataFixtures;

use App\Entity\PostOffice;
use App\Entity\ShippingZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PostNumbersFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['all'];
    }

    public function load(ObjectManager $manager): void
    {
        $zones = [];
        $zoneNames = ['Islands', 'Coast', 'Mountains', 'East', 'Croatia'];

        foreach ($zoneNames as $name) {
            $shippingZone = new ShippingZone();
            $shippingZone->setName($name);

            if ($name === 'Islands') {
                $shippingZone->setZoneSurcharge(5);
                $shippingZone->setBaseDeliveryDays(4);
            } else {
                $shippingZone->setZoneSurcharge(1);
                $shippingZone->setBaseDeliveryDays(2);
            }

            $manager->persist($shippingZone);
            $zones[$name] = $shippingZone;
        }

        $lines = explode("\n", trim($this->data));

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $parts = explode(',', $line);
            if (count($parts) === 2) {
                $name = trim($parts[0]);
                $postalCode = trim($parts[1]);
                $intCode = (int) $postalCode;

                $targetZone = 'Croatia';

                if ($this->isIsland($name, $intCode)) {
                    $targetZone = 'Islands';
                } elseif ($this->isEast($intCode)) {
                    $targetZone = 'East';
                } elseif ($this->isMountain($intCode)) {
                    $targetZone = 'Mountains';
                } elseif ($this->isCoast($intCode)) {
                    $targetZone = 'Coast';
                }

                $postOffice = new PostOffice();
                $postOffice->setName($name);
                $postOffice->setPostalCode($postalCode);
                $postOffice->setShippingZone($zones[$targetZone]);
                $manager->persist($postOffice);
            }
        }
        $manager->flush();
    }

    private function isIsland(string $name, int $code): bool
    {
        $lowercaseName = mb_strtolower($name, 'UTF-8');

        $islandKeywords = ['krk', 'cres', 'lošinj', 'rab', 'pag', 'brač', 'hvar', 'vis', 'korčula', 'lastovo', 'mljet', 'šolta', 'murter', 'pašman', 'ugljan', 'dugi otok'];
        foreach ($islandKeywords as $keyword) {
            if (str_contains($lowercaseName, $keyword)) {
                return true;
            }
        }

        return ($code >= 21400 && $code <= 21485) // Brač, Hvar, Vis, Šolta
            || ($code >= 20221 && $code <= 20226) // Elafiti i Mljet
            || ($code >= 20260 && $code <= 20275) // Korčula
            || $code === 20290                    // Lastovo
            || ($code >= 22240 && $code <= 22244) // Tisno, Murter, Betina
            || ($code >= 22231 && $code <= 22236) // Krapanj, Zlarin, Prvić, Kaprije, Žirje
            || ($code >= 23212 && $code <= 23212) // Tkon
            || ($code >= 23249 && $code <= 23251) // Pag, Kolan, Povljana
            || ($code >= 23262 && $code <= 23296) // Ugljan, Pašman, Dugi Otok, Silba, Olib, Molat...
            || ($code >= 51500 && $code <= 51564) // Krk, Cres, Lošinj
            || ($code >= 53291 && $code <= 53297); // Novalja i sjeverni Pag
    }

    private function isEast(int $code): bool
    {
        return $code >= 31000 && $code <= 35999;
    }

    private function isMountain(int $code): bool
    {
        $gorskiKotar = ($code >= 51300 && $code <= 51329);

        $lika = ($code >= 53000 && $code <= 53289);

        return $gorskiKotar || $lika;
    }

    private function isCoast(int $code): bool
    {
        return ($code >= 20000 && $code <= 23999) // Dalmacija obala
            || ($code >= 51000 && $code <= 51299) // Rijeka i Primorje obala
            || ($code >= 52000 && $code <= 52999); // Istra
    }
    // https://github.com/acosonic/postanski/blob/main/index.js
    // https://data.gov.hr/ckan/dataset/naselje-i-odredisni-postanski-ured
    private string $data = <<<EOT
Zagreb,10000
Zagreb,10112
Zagreb-Sloboština,10010
Zagreb,10146
Velika Mlaka,10408
Zagreb-Novi Zagreb,10020
Zagreb-Dubrava,10040
Zagreb-Susedgrad,10090
Zagreb,10147
Zagreb,10111
Zagreb,10104
Zagreb,10109
Zagreb,10110
Zagreb,10123
Zagreb,10135
Zagreb,10172
Zagreb,10163
Lučko,10250
Gornji Stupnik,10255
Rakov Potok,10436
Brezovica,10257
Donji Dragonožec,10253
Donja Pušća,10294
Zaprešić,10290
Bistra,10298
Kupljenovo,10295
Dubravica,10293
Prigorje Brdovečko,10291
Marija Gorica,10299
Šenkovec,10292
Jakovlje,10297
Luka,10296
Kloštar Ivanić,10312
Graberje Ivanićko,10313
Ivanić Grad,10310
Lijevi Dubrovčak,10316
Posavski Bregi,10311
Rugvica,10372
Križ,10314
Novoselec,10315
Preseka,10346
Rakovec,10347
Vrbovec,10340
Gradec,10345
Lonjica,10341
Sesvete-Kraljevec,10361
Dubrava,10342
Farkaševac,10344
Nova Kapela,10343
Sesvete,10360
Ivanja Reka,10373
Kašina,10362
Dugo Selo,10370
Donja Zelina,10382
Bedenica,10381
Sveti Ivan Zelina,10380
Komin,10383
Orle,10411
Velika Gorica,10410
Buševec,10417
Dubranec,10418
Donja Lomnica,10412
Novo Čiče,10415
Vukovina,10419
Kravarsko,10413
Pokupsko,10414
Bregana,10432
Samobor,10430
Strmec,10434
Sveta Nedelja,10431
Bestovje,10437
Sveti Martin pod Okićem,10435
Gorica Svetojanska,10453
Jastrebarsko,10450
Pisarovina,10451
Kostanjevac,10455
Kalje,10456
Krašić,10454
Sošice,10457
Ozalj,47280
Petrovsko,49234
Krapina,49000
Radoboj,49232
Lepajci,49224
Veliko Trgovišće,49214
Zabok,49210
Tuhelj,49215
Desinić,49216
Krapinske Toplice,49217
Pregrada,49218
Brestovec Orehovički,49228
Bedekovčina,49221
Poznanovec,49222
Mače,49251
Sveti Križ Začretje,49223
Gornje Jesenje,49233
Đurmanec,49225
Hum na Sutli,49231
Gornja Stubica,49245
Donja Stubica,49240
Stubičke Toplice,49244
Oroslavje,49243
Marija Bistrica,49246
Zlatar Bistrica,49247
Mihovljan,49252
Novi Golubovec,49255
Belec,49254
Zlatar,49250
Lobor,49253
Konjščina,49282
Hrašćina-Trgovišće,49283
Budinščina,49284
Klanjec,49290
Kumrovec,49295
Zagorska Sela,49296
Kraljevec na Sutli,49294
MALA SUBOTICA,40321
ČAKOVEC,40000
PODTUREN,40317
MACINEC,40306
Lopatinec,40311
NEDELIŠĆE,40305
BELICA,40319
DEKANOVEC,40318
OREHOVICA,40322
Selnica,40314
Štrigova,40312
Sveti Martin na Muri,40313
Mursko Središće,40315
Vratišinec,40316
Donja Dubrava,40328
Donji Kraljevec,40320
Sveta Marija,40326
Donji Vidovec,40327
Goričan,40324
Kotoriba,40329
Prelog,40323
Vidovec,42205
Varaždin,42000
Beretinec,42201
Sračinec,42209
Jalžabet,42203
Trnovec Bartolovečki,42202
Sveti Ilija,42214
Turčin,42204
Cestica,42208
Petrijanec,42206
Ljubešćica,42222
Novi Marof,42220
Bisag,42226
Breznički Hum,42225
Visoko,42224
Varaždinske Toplice,42223
Ludbreg,42230
Martijanec,42232
Mali Bukovec,42231
Sveti Đurđ,42233
Klenovnik,42244
Radovan,42242
Ivanec,42240
Donja Voća,42245
Maruševec,42243
Vinica,42207
Lepoglava,42250
Bednja,42253
Donja Višnjica,42255
Trakošćan,42254
Ivanska,43231
Kapela,43203
Bjelovar,43000
Veliko Trojstvo,43226
Rovišće,43212
Berek,43232
Gudovac,43251
Zrinski Topolovac,43202
Šandrovac,43227
Severin,43274
Narta,43247
Čazma,43240
Štefanje,43246
Gornji Draganec,43245
Velika Pisanica,43271
Nova Rača,43272
Veliki Grđevac,43270
Bulinac,43273
Velika Trnovitica,43285
Garešnica,43280
Uljanik,43507
Hercegovac,43284
Kaniška Iva,43283
Trnovitički Popovac,43233
Veliko Vukovje,43282
Grubišno Polje,43290
Đulovac,43532
Ivanovo Selo,43504
Veliki Zdenci,43293
Daruvar,43500
Dežanovac,43506
Sirač,43541
Končanica (Končenice),43505
Sela,44273
Martinska Ves,44201
Sisak,44000
Topolovac,44202
Gušće,44203
Sisak-Caprag,44010
Sunja,44210
Petrinja,44250
Lekenik,44272
Mošćenica,44253
Popovača,44317
Voloder,44318
Velika Ludina,44316
Kutina,44320
Banova Jaruga,44321
Husain,44326
Repušnica,44319
Jasenovac,44324
Novska,44330
Lipovljani,44322
Rajić,44323
Glina,44400
Topusko,44415
Vrginmost,44410
Hrvatska Kostajnica,44430
Hrvatska Dubica,44450
Dvor,44440
Ribnik,47272
Netretić,47271
Karlovac,47000
Lasinja,47206
Žakanje,47276
Draganić,47201
Mahićno,47286
KRNJAK,47242
VOJNIĆ,47220
SLUNJ,47240
RAKOVICA,47245
CETINGRAD,47222
BARILOVIĆ,47252
BOSILJEVO,47251
DUGA RESA,47250
GENERALSKI STOL,47262
OGULIN,47300
KAMANJE,47282
PLAŠKI,47304
SABORSKO,47306
JOSIPDOL,47303
TOUNJ,47264
Sokolovac,48306
Rasinja,48312
Koprivnica,48000
Koprivnički Ivanec,48314
Gola,48331
Ždala,48332
Sveti Ivan Žabno,48214
Križevci,48260
Orehovec,48267
Gornja Rijeka,48268
Legrad,48317
Drnje,48322
Đelekovec,48316
Hlebine,48323
Peteranec,48321
Kalinovac,48361
Ferdinandovac,48356
Kloštar Podravski,48362
Đurđevac,48350
Molve,48327
Novigrad Podravski,48325
Virje,48326
Novo Virje,48355
Podravske Sesvete,48363
Dubrovnik,20000
Mlini,20207
Cavtat,20210
Čilipi,20213
Gruda,20215
Dubravka,20216
Koločep,20221
Lopud,20222
Šipanska Luka,20223
Babino Polje,20225
Goveđari,20226
Maranovići,20224
Ston,20230
Topolo,20205
Slano,20232
Zaton Veliki,20235
Mokošica,20236
Oskorušno,20242
Trpanj,20240
Trstenik,20245
Kuna,20243
Potomje,20244
Žuljana,20247
Janjina,20246
Putniković,20248
Kučište,20267
Lovište,20269
Orebić,20250
Čara,20273
Korčula,20260
Lumbarda,20263
Pupnat,20274
Račišće,20264
Žrnovo,20275
Vela Luka,20270
Blato,20271
Smokvica,20272
Lastovo,20290
Komin (Dalmacija),20344
Ploče,20340
Rogotin,20343
Staševica,20345
Metković,20350
Mlinište,20353
Nova Sela,20278
Kula Norinska,20341
Otrić Seoci,20342
Vid,20352
Blace,20357
Opuzen,20355
Klek,20356
Split,21000
Srinjine,21292
Tugare,21252
Stobreč,21311
Donje Ogorje,21206
Donji Muć,21203
Neorić,21247
Dugopolje,21204
Lećevica,21202
Klis,21231
Solin,21210
Kaštel Sućurac,21212
Kaštel Gomilica,21213
Kaštel Kambelovac,21214
Kaštel Lukšić,21215
Kaštel Stari,21216
Kaštel Štafilić,21217
Slatine,21224
Primorski Dolac,21227
Trogir,21220
Prgomet,21201
Okrug Gornji,21223
Seget Donji,21218
Blizna Donja,21228
Marina,21222
Vinišće,21226
Drvenik Veliki,21225
Sinj,21230
Dicmo,21232
Hrvace,21233
Vrlika,21236
Otok (Dalmacija),21238
Obrovac Sinjski,21241
Ugljane,21243
Trilj,21240
Cista Velika,21244
Šestanovac,21250
Aržano,21246
Cista Provo,21256
Lovreć,21257
Krivodol,21263
Runović,21261
Donji Proložac,21264
Imotski,21260
Zmijavci,21266
Zagvozd,21270
Slivno,21272
Vrgorac,21276
Dragljane,21275
Veliki Prolog,21277
Makarska,21300
Tučepi,21325
Omiš,21310
Mimice,21318
Podstrana,21312
Dugi Rat,21315
Baška Voda,21320
Brela,21322
Podgora,21327
Drašnice,21328
Igrane,21329
Drvenik,21333
Podaca,21335
Gradac,21330
Zaostrog,21334
Postira,21410
Nerežišća,21423
Supetar,21400
Sutivan,21403
Ložišća,21404
Milna,21405
Pražnica,21424
Pučišća,21412
Bol,21420
Selca,21425
Povlja,21413
Sumartin,21426
Grohote,21430
Hvar,21450
Brusje,21454
Stari Grad,21460
Vrbanj,21462
Jelsa,21465
Vrboska,21463
Zastražišće,21466
Gdinj,21467
Sućuraj,21469
Vis,21480
Komiža,21485
Perković,22205
Zaton,22215
Boraja,22206
Šibenik,22000
Šibenik-Brodarica,22010
Šibenik-Ražine,22020
Krapanj,22231
Rogoznica,22203
Široke,22204
Primošten,22202
Vodice,22211
Tribunj,22212
Čista Velika,22214
Pirovac,22213
Skradin,22222
Lozovac,22221
Zlarin,22232
Prvić Luka,22233
Prvić Šepurine,22234
Kaprije,22235
Žirje,22236
Tisno,22240
Jezera,22242
Betina,22244
Murter,22243
Knin,22300
Zvjerinac(Kosovo),22312
Kistanje,22305
Kijevo,22310
Đevrske,22319
Ervenik,22306
Golubić,22301
Mokro Polje,22307
Pađene,22318
Plavno,22317
Polača,22302
Radučić,22304
Strmica,22311
Oklaj,22303
Ružić,22322
Siverić,22321
Drniš,22320
Drinovci,22324
Unešić,22323
Zadar,23000
Petrčane,23231
Bibinje,23205
Sukošan,23206
Zemunik,23222
Polača,23423
Sveti Filip i Jakov,23207
Biograd na Moru,23210
Pakoštane,23211
Tkon,23212
Pašman,23262
Neviđane,23264
Škabrnja,23223
Nin,23232
Privlaka (Dalmacija),23233
Vir,23234
Vrsi,23235
Poličnik,23241
Posedarje,23242
Vinjerac,23247
Jasenice,23243
Starigrad Paklenica,23244
Tribanj,23245
Ražanac,23248
Povljana,23249
Pag,23250
Kolan,23251
Ždrelac,23263
Kukljica,23271
Preko,23273
Lukoran,23274
Ugljan,23275
Žman,23282
Sali,23281
Rava,23283
Veli Iž,23284
Božava,23286
Veli Rat,23287
Brbinj,23285
Sestrunj,23291
Molat,23292
Ist,23293
Premuda,23294
Silba,23295
Olib,23296
Novigrad (Dalmacija),23312
Pridraga,23226
Benkovac,23420
Bjelina,23421
Smilčić,23424
Stankovci,23422
Gračac,23440
Lovinac,53244
Bruvno,23441
Zrmanja,23443
Otrić,23442
Obrovac,23450
Žegar,23451
Karin,23452
Lički Osik,53201
Medak,53205
Gospić,53000
Klanac,53212
Brušane,53206
Smiljan,53211
Donje Pazarište,53213
Perušić,53202
Kosinj,53203
Dabar,53222
Škare,53221
Otočac,53220
Brlog,53226
Bunić,53235
Ličko Lešće,53224
Vrhovine,53223
Krasno,53274
Švica,53225
Plitvička Jezera,53231
Korenica,53230
Ličko Petrovo Selo,53233
Udbina,53234
Podlapača,53236
Srb,23445
Doljani,53252
Nebljusi,53251
Donji Lapac,53250
Kaldrma,23446
Brinje,53260
Vratnik,53273
Križpolje,53261
Jezerane,53262
Lukovo,53285
Starigrad,53286
Senj,53270
Krivi Put,53271
Sveti Juraj,53284
Jablanac,53287
Karlobag,53288
Lukovo Šugarje,53289
Zubovići,53296
Novalja,53291
Gajac,53295
Lun,53294
Stara Novalja,53297
Bilje,31327
Lug (Laskó),31328
Osijek,31000
Laslovo,31214
Tenja,31207
Antunovac,31216
Ernestinovo,31215
Josipovac,31221
Višnjevac,31220
Aljmaš,31205
Bijelo Brdo,31204
Dalj,31226
Erdut,31206
Duboševica,31304
Kneževi Vinogradi,31309
Batina,31306
Beli Manastir,31300
Bolman,31323
Branjin Vrh,31301
Popovac,31303
Čeminac,31325
Draž,31305
Jagodnjak,31324
Karanac,31315
Kneževo,31302
Petlovac,31321
Suza,31308
Zmajevac (Vörösmart),31307
Darda,31326
Semeljci,31402
Drenje,31418
Levanjska Varoš,31416
Bračevci,31423
Đakovo,31400
Trnava,31411
Satnica Đakovačka,31421
Gorjani,31422
Punitovci,31424
Strizivojna,31410
Piškorevci,31417
Selci Đakovački,31415
Viškovci,31401
Vuka,31403
Čepin,31431
Vladislavci,31404
Koška,31224
Đurđenovac,31511
Podgorač,31433
Našice,31500
Breznica Našička,31225
Budimci,31432
Donja Motičina,31513
Feričanci,31512
Magadenovac,31542
Donji Miholjac,31540
Podgajci Podravski,31552
Viljevo,31531
Miholjački Poreč,31543
Podravska Moslavina,31530
Bizovac,31222
Valpovo,31550
Brođanci,31223
Zelčin,31227
Petrijevci,31208
Baranjsko Petrovo Selo,31322
Belišće,31551
Črnkovci,31553
Gat,31554
Marijanci,31555
Bobota,32225
Vukovar,32000
Borovo,32227
Bršadin,32222
Vukovar,32010
Negoslavci,32239
Sotin,32232
Trpinja,32224
Rokovci Andrijaševci,32271
Tordinci,32214
Nuštar,32221
Gaboš,32212
Ivankovo,32281
Jarmina,32280
Markušica,32213
Vinkovci,32100
Ostrovo,32211
Retkovci,32282
Bapska,32235
Ilok,32236
Šarengrad,32234
Opatovac,32233
Čakovci,32238
Orolik,32243
Stari Jankovci,32241
Petrovci,32229
Slakovci,32242
Lovas,32237
Banovci,32247
Ilača,32248
Tovarnik,32249
Otok,32252
Nijemci,32245
Privlaka,32251
Lipovac,32246
Đeletovci,32244
Komletinci,32253
Drenovci,32257
Đurići,32263
Gunja,32260
Posavski Podgajci,32258
Račinovci,32262
Rajevo Selo,32261
Soljani,32255
Strošinci,32256
Vrbanja,32254
Babina Greda,32276
Bošnjaci,32275
Cerna,32272
Gradište,32273
Štitar,32274
Županja,32270
Stari Mikanovci,32284
Vođinci,32283
Gornje Bazje,33407
Virovitica,33000
Lukač,33406
Špišić Bukovica,33404
Pitomača,33405
Gradina,33411
Pivnica Slavonska,33533
Suhopolje,33410
Cabuna,33412
Crnac,33507
Mikleuš,33517
Zdenci,33513
Orahovica,33515
Čačinci,33514
Slatina,33520
Sopje,33525
Voćin,33522
Nova Bukovica,33518
Čađavica,33523
Brestovac,34322
Požega,34000
Jakšić,34308
Velika,34330
Kaptol,34334
Kuzmica,34311
Sesvete (kod Požege),34312
Pleternica,34310
Ratkovica,34315
Vetovo,34335
Čaglin,34350
Kutjevo,34340
Bektež,34343
Poljana,34543
Lipik,34551
Bučje,34553
Pakrac,34550
Badljevina,34552
Bebrina,35254
Podcrkavlje,35201
Slavonski Brod,35000
Bukovlje,35209
Ruščica,35208
Gornja Vrba,35207
Podvinje,35107
Garčin,35212
Trnjani,35211
Oprisavci,35213
Vrpolje,35210
Donji Andrijevci,35214
Prnjavor,35216
Slavonski Šamac,35220
Sikirevci,35224
Velika Kopanica,35221
Gundinci,35222
Oriovac,35250
Slavonski Kobaš,35255
Sibinj,35252
Brodski Stupnik,35253
Cernik,35404
Nova Gradiška,35400
Rešetari,35403
Davor,35425
Vrbje,35423
Orubica,35424
Lužani,35257
Nova Kapela,35410
Vrbova,35414
Staro Petrovo Selo,35420
Zapolje,35422
Dragalić,35428
Gornji Bogićevci,35429
Okučani,35430
Stara Gradiška,35435
Kostrena,51221
Rijeka,51000
Šapjane,51214
Matulji,51211
Jurdani,51213
Vele Mune,51212
Kastav,51215
Viškovo,51216
Klana,51217
Dražice,51218
Čavle,51219
Šušnjevica,52333
Hreljin,51226
Praputnjak,51225
Krasica,51224
Kukuljanovo,51227
Škrljevo,51223
Ledenice,51251
Klenovica,51252
Bribir,51253
Novi Vinodolski,51250
Crikvenica,51260
Dramalj,51265
Grižane,51244
Jadranovo,51264
Selce,51266
Tribalj,51243
Bakar,51222
Bakarac,51261
Drivenik,51242
Kraljevica,51262
Križišće,51241
Šmrika,51263
Rab,51280
Lopar,51281
Mrkopalj,51315
Crni Lug,51317
Brod na Kupi,51301
Vrata,51321
Fužine,51322
Skrad,51311
Kupjak,51313
Delnice,51300
Kuželj,51302
Ravna Gora,51314
Lokve,51316
Lič,51323
Zlobin,51324
Prezid,51307
Tršće,51305
Čabar,51306
Plešce,51303
Gerovo,51304
Lukovdol,51328
Brod Moravice,51312
Moravice,51325
Severin na Kupi,51329
Gomirje,51327
Vrbovsko,51326
Ičići,51414
Opatija,51410
Lovran,51415
Brseč,51418
Mošćenička Draga,51417
Malinska,51511
Vrbnik,51516
Kornić,51517
Krk,51500
Dobrinj,51514
Njivice,51512
Omišalj,51513
Šilo,51515
Punat,51521
Baška,51523
Draga Bašćanska,51522
Belej,51555
Ćunski,51564
Mali Lošinj,51550
Nerezine,51554
Veli Lošinj,51551
Ilovik,51552
Martinšćica,51556
Beli,51559
Cres,51557
Susak,51561
Unije,51562
Pazin,52000
Cerovlje,52402
Tinjan,52444
Gračišće,52403
Pićan,52332
Pula (Pola),52100
Ližnjan (Lisignano),52204
Fažana (Fasana),52212
Medulin,52203
Barban,52207
Krnica,52208
Marčana,52206
Bale (Valle),52211
Kanfanar,52352
Rovinj (Rovigno),52210
Vodnjan (Dignano),52215
Galižana,52216
Labin,52220
Trget,52224
Koromačno,52222
Nedešćina,52231
Plomin,52234
Raša,52223
Rabac,52221
Kršan,52232
Podpićan,52333
Šušnjevica,52233
Žminj,52341
Svetvinčenat,52342
Sveti Petar u šumi,52404
Buzet,52420
Boljun,52434
Roč,52425
Slum,52421
Lupoglav,52426
Lanišće,52422
Oprtalj (Portole),52428
Motovun (Montona),52424
Livade (Levade),52427
Karojba,52423
Poreč (Parenzo),52440
Višnjan (Visignano),52463
Kaštelir (Castelliere),52464
Baderna,52445
Vižinada (Visinada),52447
Červar Porat,52449
Vrsar (Orsera),52450
Nova Vas,52446
Sveti Lovreč,52448
Tar (Torre),52465
Funtana (Fontane),52452
Buje (Buie),52460
Grožnjan (Grisignana),52429
Momjan (Momiano),52462
Novigrad (Cittanova),52466
Brtonigla (Verteneglio),52474
Umag (Umago),52470
Savudrija (Salvore),52475
EOT;
}
