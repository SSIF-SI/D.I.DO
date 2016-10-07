package dido.signatureTests;


import dido.pdfmanager.PdfManager;

public class SignatureTest1 {
	public static final String SRC = "/testresources/sample06.pdf";
	public static void main(String[] args) {
		PdfManager sigMan=new PdfManager();
		sigMan.loadPDF(SRC);
	}

}
