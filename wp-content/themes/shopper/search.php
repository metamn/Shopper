<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div>
		<input type="text" class="field" name="s" id="s" placeholder="Peste 300 de cadouri" />
		<input type="submit" class="submit" name="submit" id="searchsubmit" value="Cautare" />
	</div>
	<ul>
    <li><input id="search-price-1" type="radio" name="price" value="0-100000" checked/>
      <label for="search-price-1"></label> Cautare fara pret</li>
    <li><input id="search-price-2" type="radio" name="price" value="0-100"/>
      <label for="search-price-2"></label> < 100 RON</li>
    <li><input id="search-price-3" type="radio" name="price" value="100-250" />
      <label for="search-price-3"></label> 100 - 250 RON</li>
    <li><input id="search-price-4" type="radio" name="price" value="250-350" />
      <label for="search-price-4"></label> 250 - 350 RON</li>
    <li><input id="search-price-5" type="radio" name="price" value="350" />
      <label for="search-price-5"></label> Banii nu conteaza!</li>  
  </ul>
</form>
