
<div class="ft_footer">
    <div class="ft_container">
        <div class="ft_about">
            <h4>О магазине</h4>
            <ul>
                <li><a href="">Отзывы</a></li>
                <li><a href="">Контакты</a></li>
                <li><a href="">Руководство</a></li>
                <li><a href="">История</a></li>
            </ul>
        </div>
        <div class="ft_catalog">
            <h4>Каталог товаров</h4>
            <ul>
                <li><a href="">Кухни</a></li>
                <li><a href="">Кровати и кушетки</a></li>
                <li><a href="">Гарнитуры</a></li>
                <li><a href="">Тумобчки и прихожие</a></li>
                <li><a href="">Спальни и матрасы</a></li>
                <li><a href="">Аксессуары</a></li>
                <li><a href="">Столы и стулья</a></li>
                <li><a href="">Каталоги мебели</a></li>
                <li><a href="">Раскладные диваны</a></li>
                <li><a href="">Кресла</a></li>
            </ul>

        </div>
        <div class="ft_contacts">
            <h4> <?=GetMessage("CONTACT_INFO");?></h4>
            <!-- vCard        http://help.yandex.ru/webmaster/hcard.pdf      -->
            <p class="vcard">
						<span class="adr">
							<span class="street-address"><?$APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    Array(
                                        "AREA_FILE_SHOW" => "file",
                                        "PATH" => "/include/addr.php",
                                        "EDIT_TEMPLATE" => ""
                                    ),
                                    false
                                );?></span>
						</span>
                <span class="tel"><?$APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        Array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => "/include/phone.php",
                            "EDIT_TEMPLATE" => ""
                        ),
                        false
                    );?></span>
                <strong>Время работы:</strong> <br/> <span class="workhours">ежедневно с 9-00 до 18-00</span><br/>
            </p>
            <?$APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                Array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => "/include/social.php",
                    "EDIT_TEMPLATE" => ""
                ),
                false
            );?>
            <div class="ft_copyright">© 2000 - 2012 "Мебельный магазин" </div>


        </div>

        <div class="clearboth"></div>
    </div>
</div>