package dido.signatureTests;


import dido.signature.SignatureManager;

public class SignatureTest1 {
	public static final String SRC = "/testresources/sample06.pdf";
	public static void main(String[] args) {
		SignatureManager sigMan=new SignatureManager();
		sigMan.loadPDF(SRC);
	}

}
