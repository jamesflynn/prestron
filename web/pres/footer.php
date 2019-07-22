									</p>
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript -->

    <script>
    	function validateForm() {
	
    if (document.forms["myform"]["usetwilio"].checked) {
    	if (document.forms["myform"]["Sender"].value == <?php echo "'".$rando."'" ?>)
      		return false;  // don't let em text a rando
    	else
 		    return confirm('This will send texts, are you sure?');
    }

    else return true;
}
</script>

  </body>

</html>