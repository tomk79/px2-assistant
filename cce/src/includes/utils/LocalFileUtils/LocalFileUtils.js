export class LocalFileUtils {

	async readLocalFile(fileInfo){
		return new Promise((resolve, reject) => {
			var reader = new FileReader();
			reader.onload = function(evt) {
				resolve( evt.target.result );
			}
			reader.readAsDataURL(fileInfo);
		});
	}

}
