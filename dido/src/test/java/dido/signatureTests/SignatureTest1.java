package dido.signatureTests;

//Tipo A.pdf Tipo A-1 commento.pdf	Tipo A-2 comment1.pdf Tipo A-ex 2 commenti.pdf Tipo NON A.pdf

import java.io.IOException;

import com.itextpdf.text.DocumentException;

import dido.pdfmanager.PdfManager;

public class SignatureTest1 {
	public static final String SRC = "/testresources/TipoA.pdf";	
	public static final String SRC1 = "/testresources/TipoA-1commento.pdf";
	public static final String SRC2 = "/testresources/TipoA-2comment1.pdf";
	public static final String SRC3 = "/testresources/TipoA-ex2commenti.pdf";
	public static final String SRC4 = "/testresources/TipoNONA.pdf";
	public static final String SRC5 = "/testresources/sample06.pdf";


	public static void main(String[] args) throws IOException, DocumentException {
		System.out.println("INIZIO TEST");
		PdfManager sigMan=new PdfManager();
		sigMan.loadPDF(SRC);
		sigMan.getAnnotations();
		
		sigMan.loadPDF(SRC1);
		sigMan.getAnnotations();
		
		sigMan.loadPDF(SRC2);
		sigMan.getAnnotations();

		sigMan.loadPDF(SRC3);
		sigMan.getAnnotations();

		sigMan.loadPDF(SRC4);
		sigMan.getAnnotations();


		sigMan.loadPDF(SRC5);
		sigMan.getAnnotations();


	}

}
